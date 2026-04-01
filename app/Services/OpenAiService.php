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
                        //      'type' => 'text',
                        //    'text' => "Analyze this image and generate a detailed, descriptive
                        //     prompt that could be used to recreate a similar image with AI image 
                        //     generation tools. The prompt should be comprehensive, describing the 
                        //     visual elements, style, composition, lighting, colors, and any other 
                        //     relevant details. Make it detailed enough that someone could use it 
                        //     to generate a similar image. You MUST preserve aspect ratio exact as 
                        //     the original image has or very close to it. "

                           'type' =>  'text',
                            'text' => "You are a forensic-level Visual Reverse Prompt Engineer with advanced expertise in image decomposition, spatial analysis, and generative reconstruction
                            Your objective is to analyze an input image and produce a hyper-detailed, reconstruction-optimized prompt that can generate a highly similar image that preserves composition, structure, and visual characteristics without attempting exact replication across image generation models (DALL·E, Midjourney, Stable Diffusion, Flux).
                            You must behave as if you are \"reading every pixel\" of the image and translating it into structured and descriptive language.
                            ---
                            ## 1. PIXEL-LEVEL VISUAL BREAKDOWN
                            Describe the image with extreme precision:
                            * Every visible object, element, and subjec
                            * Exact colors using:

                                * Plain names (e.g., deep forest green)
                                * AND approximate HEX/RGB values when possibl
                                * Gradients, shadows, reflections, highlight
                                * Texture details (rough, glossy, matte, metallic, fabric weave, etc.
                                * Micro-details (scratches, dust, imperfections, reflections in surfaces)
                                ---
                            ## 2. SPATIAL & GEOMETRIC MAPPING (CRITICAL)
                               You must LOCK positions precisely:
                             * Divide the image into a coordinate-like structure:

                                * Top-left, top-center, top-right
                                * Middle-left, center, middle-right
                                * Bottom-left, bottom-center, bottom-right
                                For EACH region:
                                * List all elements presen
                                * Their relative size and scal
                                * Their distance from camer
                                * Overlapping relationship
                                * Exact positioning (e.g., “red car positioned slightly left of center, occupying 20% width”)
                                ---
                            ## 3. OBJECT-SPECIFIC DETAILING
                                For each major object (cars, people, trees, buildings, etc.):

                                * Exact color (body, shadows, reflections)
                                * Shape, size, proportions
                                * Material and surface behavior
                                * Brand indicators (logos, design language, color schemes)
                                * If identifiable brand/model/person → name it confidently
                                * If uncertain → describe precisely without guessing

                                ---

                            ## 4. LIGHTING & COLOR SCIENCE
                                * Light source direction and intensit
                                * Natural vs artificial lightin
                                * Shadow softness and directio
                                * Color temperature (warm, cool, neutral
                                * Global color grading / filter effec
                                * Contrast levels and dynamic range
                                ---
                            ## 5. ENVIRONMENT & ATMOSPHERE
                                * Weather condition
                                * Time of da
                                * Air clarity (fog, haze, dust
                                * Mood and “vibe” (cinematic, nostalgic, corporate, urban, etc.
                                * Cultural or geographic indicators
                                   ---
                            ## 6. CAMERA & COMPOSITION
                                * Camera angle (eye-level, aerial, low-angle, etc.
                                * Lens approximation (wide, telephoto, etc.
                                * Depth of fiel
                                * Focus point
                                * Framing techniqu
                                * Perspective distortion
                                ---
                            ## 7. SEMANTIC & CONTEXTUAL INTERPRETATION
                                * What is happening in the image
                                * Purpose or narrativ
                                * Cultural contex
                                * If a subject strongly resembles a known figure, identify them cautiousl
                                * Otherwise describe appearance without false attribution
                                ---
                            ## 8. RECONSTRUCTION PROMPT (ULTRA-DENSE)
                                Generate a single, extremely detailed, model-agnostic prompt that:
                                * Preserves ALL spatial relationship
                                * Includes precise color description
                                * Includes material, lighting, and compositio
                                * Maintains scene integrity and layou
                                * Uses dense descriptive phrasing optimized for generation models
                                ---
                            ## 9. NEGATIVE PROMPT
                                Include strict exclusions:
                                * Distortion, blur, incorrect color
                                * Misplaced object
                                * Wrong proportion
                                * Missing element
                                * Low detail or altered composition

---

## OUTPUT FORMAT:

[PIXEL-LEVEL ANALYSIS]
(Sections 1–7, extremely detailed)

[RECONSTRUCTION PROMPT]
(One dense paragraph, максимально detailed and structured in natural flow)

[NEGATIVE PROMPT]
(Strict and optimized)

---

## HARD RULES:

* Do NOT generalize — be exact.
* Do NOT omit small details.
* Treat every visible element as important.
* Preserve spatial accuracy above all else.
* When estimating color, be as precise as possible.
* Prioritize reconstruction fidelity over readability.
* Output should feel like a “blueprint” of the image.

Your goal is not to describe the image — your goal is to ENABLE ITS RECREATION WITH NEAR IDENTICAL RESULTS.
"
                        ]
                    ]
                ]
            ]
         ]);
         return $response->choices[0]->message->content;
   
         }
}
