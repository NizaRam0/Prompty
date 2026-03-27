<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\OpenAIService;
use App\Models\PromptGeneration;
use App\Http\Resources\PromptGenerationResource;
class PromptGenerationController extends Controller

{
    public function __construct(private OpenAIService $openAIService)
    {
    }
    public function index() //return all image generations
    {
        $user=request()->user();
        $PromptGeneration=$user->PromptGenerations()->latest()->paginate(10);
        return PromptGenerationResource::collection($PromptGeneration);
    }

    public function store(Request $request) //generate image from prompt
    {
        $user = $request->user();
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
         * @var string $extension The file extension of the uploaded image
         */
        $safeFileName = $sanitizedName . '_' . time() .'_'.Str::random(10). '.' . $extension;
        $imagePath=$image->storeAs('uploads/images', $safeFileName,'public');

        $this->openAIService->generatePromptForImage($image);
         $generatedPrompt = $this->openAIService->generatePromptForImage($image);
         $promptGeneration= $user->PromptGenerations()->create([
            'generated_prompt' => $generatedPrompt,
            'image_path' => $imagePath,
            'original_file_name' => $originalName,
            'file_size' => $image->getSize(),
            'mime_type' => $image->getMimeType(),
         ]);
         return new PromptGenerationResource($promptGeneration);
    
        }
}
