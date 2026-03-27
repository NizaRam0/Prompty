<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PromptGenerationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'image_url' => $this->image_path ? asset('storage/' . $this->image_path) : null,
            'generated_prompt' => $this->generated_prompt,
            'original_file_name' => $this->original_file_name,
            'file_size' => $this->file_size,
            'mime_type' => $this->mime_type,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
             'user' => new UserResource($this->whenLoaded('user')) // Assuming you have a relationship defined in your PromptGeneration model to get the user details
         ];
    }
}
/**
 * ImageGenerationResource
 * 
 * API Resource class for transforming ImageGeneration model data into JSON responses.
 * 
 * This resource is responsible for:
 * - Formatting image generation records as structured JSON data for API endpoints
 * - Serializing model attributes including id, image_url, prompt, and timestamps
 * - Formatting datetime fields to a consistent 'Y-m-d H:i:s' format
 * - Conditionally including related user data through lazy loading to prevent N+1 queries
 * 
 * Usage:
 * - Return from controllers: ImageGenerationResource::collection($imageGenerations)
 * - Used to standardize API responses across multiple endpoints
 * - Ensures consistent data structure and format for frontend consumption
 * 
 * @see https://laravel.com/docs/eloquent-resources
 */
