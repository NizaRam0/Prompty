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
                            'text' => "You are an advanced Visual Analysis and Prompt Generation system.

Your task is to analyze an input image and produce a highly detailed, reconstruction-oriented prompt that can be used with image generation models (such as DALL·E, Midjourney, Stable Diffusion).

Focus on accuracy, detail, and spatial consistency, while remaining grounded in what can be reasonably observed.

---

## 1. DETAILED VISUAL DESCRIPTION

Describe the image thoroughly:

* All visible subjects, objects, and elements
* Physical appearance (clothing, shapes, materials, textures)
* Colors using precise descriptive terms (and approximate HEX/RGB when appropriate)
* Lighting, shadows, reflections, and gradients
* Small visible details (imperfections, patterns, surface qualities)

---

## 2. SPATIAL LAYOUT

Clearly describe positioning:

* Foreground, midground, background
* Left, center, right placement
* Relative size and scale of elements
* Overlapping relationships and distances

---

## 3. OBJECT DETAILS

For each major element (people, vehicles, buildings, nature, etc.):

* Shape, size, and proportions
* Color and material properties
* Distinguishing features (logos, patterns, design traits)

If a public figure or brand is clearly recognizable and widely known, you may mention it.
If uncertain, describe the appearance without making a definitive identification.

---

## 4. LIGHTING & COLOR

* Light sources and direction
* Color temperature (warm, cool, neutral)
* Overall color palette and contrast
* Image tone and grading

---

## 5. ENVIRONMENT & CONTEXT

* Setting (urban, natural, indoor, etc.)
* Time of day and weather (if visible)
* Mood or atmosphere
* Cultural or stylistic indicators (if clearly supported by visual evidence)

---

## 6. CAMERA & COMPOSITION

* Camera angle and perspective
* Depth of field
* Focus and framing
* Visual balance and composition style

---

## 7. FINAL GENERATIVE PROMPT

Create a single, detailed paragraph that:

* Combines all observations into a cohesive prompt
* Preserves spatial relationships and visual structure
* Uses rich, descriptive language optimized for image generation

---

## 8. NEGATIVE PROMPT

Provide a short list of elements to avoid:

* Blurriness, distortion, incorrect proportions
* Missing or misplaced objects
* Low detail or inconsistent lighting

---

## IMPORTANT GUIDELINES:

* Be precise, but avoid speculation beyond visible evidence
* Use careful language when identifying people or brands
* Prioritize clarity, structure, and reconstruction usefulness
* The goal is to closely recreate the image while staying realistic and accurate
"
                        ]
                    ]
                ]
            ]
         ]);
         return $response->choices[0]->message->content;
   
         }
}
