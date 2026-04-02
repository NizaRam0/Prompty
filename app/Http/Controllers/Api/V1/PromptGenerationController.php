<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\OpenAiService;
use App\Models\PromptGeneration;
use App\Http\Requests\GeneratePromptRequest;
use App\Http\Resources\PromptGenerationResource;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Support\Facades\RateLimiter;
use App\Http\Resources\UserResource;

#[Group('Prompt Generations', 'Upload images and generate prompts', 10)]
class PromptGenerationController extends Controller

{
    private const DAILY_PROMPT_LIMIT = 5;

    private const UNLIMITED_EMAILS = [
        'nizar@gmail.com',
        'elnizarramadan61@gmail.com',
    ];

    public function __construct(private OpenAiService $openAiService)
    {
    }

    #[Endpoint(title: 'List Prompt Generations', description: 'Get paginated prompt generations for the authenticated user.')]
    public function index(Request $request) //return all image generations
    {
        $user=request()->user();
//search and filter functionality
        $query = $user->PromptGenerations();
        if ($request->filled('search')) {
            $searchTerm = $request->query('search');

            $query->where(function ($q) use ($searchTerm) {
                $q->where('generated_prompt', 'like', '%' . $searchTerm . '%')
                    ->orWhere('mime_type', 'like', '%' . $searchTerm . '%');
            });
        }

        if ($request->filled('mime_type')) {
            $query->where('mime_type', $request->query('mime_type'));
        }

        //sorting
        $sorting_fields=[
            'created_at',
            'file_size', 
            'mime_type',
            'generated_prompt',
            'original_file_name'
        ];
        $sort_field= 'created_at';//default sorting field
        $sort_direction='desc';//default sorting direction
        if ($request->has('sort') && !empty($request->query('sort'))) {
           
        $sort= $request->query('sort');
            
            if(str_starts_with($sort_field,'-')){
                $sort_direction='desc';
                $sort_field= substr($sort,1); //remove the leading '-' to get the actual field name
            }else{
                    $sort_field=$sort;
                $sort_direction='asc';
            }
        }

        if(!in_array($sort_field,$sorting_fields)){
            $sort_field='created_at'; //default to created_at if invalid sort field is provided
            $sort_direction='desc';
        }

        $query->orderBy($sort_field,$sort_direction);//apply the sort 


//pagination
        $promptGenerations = $query->paginate($request->query('per_page'));

        return PromptGenerationResource::collection($promptGenerations);
    }

    //public function store(Request $request) //generate image from prompt
    #[Endpoint(title: 'Generate Prompt From Image', description: 'Upload an image file and generate a detailed prompt.')]
    public function store(GeneratePromptRequest $request) //generate image from prompt
    {
        $user = $request->user();

        if ($this->isQuotaExhausted($request)) {
            return response()->json([
                'message' => 'You have reached your daily generation limit. Please try again later.',
            ], 429);
        }

        $image = $request->file('image');
        $originalName = $image->getClientOriginalName();
        $sanitizedName= preg_replace('/[^a-zA-Z0-9_\.-]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
        // sanitize by replacing non-alphanumeric characters with underscores
        //so its safe to use in file system and URLs
        $extension = $image->getClientOriginalExtension();
         /**
         * Gets the file extension of the uploaded image.
         * 
         * Retrieves the original file extension from the client-side filename
         * (e.g., 'jpg', 'png', 'gif', 'webp') without validating the actual file type.
         * 
         */
        $safeFileName = $sanitizedName . '_' . time() .'_'.Str::random(10). '.' . $extension;
        $uploadDisk = $this->resolveUploadDisk();
        $imagePath = $image->storeAs('uploads/images', $safeFileName, $uploadDisk);

        //$this->openAiService->generatePromptForImage($image);
         $generatedPrompt = $this->openAiService->generatePromptForImage($image);
            $promptGeneration= $user->PromptGenerations()->create([
                'generated_prompt' => $generatedPrompt,
                'image_path' => $imagePath,
                'original_file_name' => $originalName,
                'file_size' => $image->getSize(),
                'mime_type' => $image->getMimeType(),
            ]);

            $this->incrementQuota($request);

            // Return both the prompt generation and the updated user quota
            return response()->json([
                 'prompt_generation' => new PromptGenerationResource($promptGeneration),
                 'user_quota' => new UserResource($user),
            ]);
          }
    #[Endpoint(title: 'Delete Prompt Generation', description: 'Delete one prompt generation that belongs to the authenticated user.')]
    public function destroy(Request $request, PromptGeneration $promptGeneration)
    {
        if ($promptGeneration->user_id !== $request->user()->id) {
            abort(404);
        }

        $promptGeneration->delete();

        return response()->json([
            'message' => 'Prompt generation deleted successfully',
        ]);
    }

    private function resolveUploadDisk(): string
    {
        $configuredDisk = config('filesystems.default', 'public');
        $availableDisks = config('filesystems.disks', []);

        if (is_string($configuredDisk)
            && $configuredDisk !== ''
            && is_array($availableDisks)
            && array_key_exists($configuredDisk, $availableDisks)
            && $configuredDisk !== 'local') {
            return $configuredDisk;
        }

        if (is_array($availableDisks)
            && array_key_exists('s3', $availableDisks)
            && !empty(config('filesystems.disks.s3.bucket'))) {
            return 's3';
        }

        return 'public';
    }

    private function isQuotaExhausted(Request $request): bool
    {
        $user = $request->user();

        if ($user && in_array($user->email, self::UNLIMITED_EMAILS, true)) {
            return false;
        }

        $key = (string) ($user?->id ?: $request->ip());

        return RateLimiter::tooManyAttempts($key, self::DAILY_PROMPT_LIMIT);
    }

    private function incrementQuota(Request $request): void
    {
        $user = $request->user();

        if ($user && in_array($user->email, self::UNLIMITED_EMAILS, true)) {
            return;
        }

        $key = (string) ($user?->id ?: $request->ip());
        
        RateLimiter::hit($key, 86400);
    }
}
