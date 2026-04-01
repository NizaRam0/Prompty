<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use OpenAI\Factory;


class OpenAiService
{
    /**
     * Create a new class instance.
     */
    public function generatePromptForImage(UploadedFile $image): string
    {
         $imageData= base64_encode(file_get_contents($image->getPathname()));
         $mimeType=$image->getMimeType();
         $client=(new Factory())->withApiKey(config('services.openai.api_key'))->make();
         $response=$client->chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => [
                [
                    'role'=>'system',
                    'content'=>'You are a helpful assistant that generates descriptive prompts for images.'
                ],
                [
                    'role' => 'user',
                    'content' =>[
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => "data:{$mimeType};base64,{$imageData}"
                            ]
                        ],
                        [
                             /*'type' => 'text',
                           'text' => "Analyze this image and generate a detailed, descriptive
                            prompt that could be used to recreate a similar image with AI image 
                            generation tools. The prompt should be comprehensive, describing the 
                            visual elements, style, composition, lighting, colors, and any other 
                            relevant details. Make it detailed enough that someone could use it 
                            to generate a similar image. You MUST preserve aspect ratio exact as 
                            the original image has or very close to it. "*/

                           'type' =>  'text',
                            'text' => "You are an expert visual analyst and prompt engineer.
                            Your task is to analyze the provided image and generate a highly detailed, structured prompt suitable for AI image generation tools (such as Midjourney, DALL·E, or Stable Diffusion).
                            Follow these rules strictly:
                            1. Describe the image with extreme specificity, covering:
                                 - Main subject(s) (appearance, pose, expression, materials)
                                 - Environment / background (setting, depth, context)
                                 - Composition (framing, angle, perspective, focal point)
                                 - Lighting (type, direction, intensity, shadows, highlights)
                                 - Color palette (dominant tones, contrast, grading style)
                                 - Style (photorealistic, cinematic, illustration, 3D, etc.)
                                 - Texture and details (surfaces, reflections, imperfections)
                                 - Camera details (lens type, focal length, depth of field, bokeh)
                                 - Mood / atmosphere (emotional tone, storytelling feel)
                                 
                                 2. Infer technical details when not explicitly visible:
                                 - Camera: (e.g., 35mm, 85mm, macro)
                                 - Lighting setup (studio, natural light, HDR, softbox, etc.)
                                 - Rendering style (Octane, Unreal Engine, film photography, etc.)
                                 
                                 3. Reconstruct this into a SINGLE, clean, generation-ready prompt:
                                 - No explanations
                                 - No bullet points
                                 - No headings
                                 - Just one continuous, well-structured prompt
                                 
                                 4. Add generation-enhancing keywords where appropriate:
                                 - \"ultra-detailed", "8k", "highly realistic", "cinematic lighting\", etc.
                                 -  But DO NOT overstuff keywords — keep it natural and coherent
                                 
                                 5. Aspect Ratio (VERY IMPORTANT):
                                 - Detect the image aspect ratio precisely (e.g., 1:1, 16:9, 4:5, 9:16)
                                 - Append it at the end in this format:
                                    --ar WIDTH:HEIGHT
                              
                                 6. Do NOT hallucinate irrelevant elements.
                                 
                                 7. Stay faithful to the image.
                                 
                                 Output ONLY the final prompt."
                        ]
                    ]
                ]
            ]
         ]);
         return $response->choices[0]->message->content;
   
         }
}
