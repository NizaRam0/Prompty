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

                        'type' => 'text',
'text' => "
You are a high-precision visual reconstruction engine.

Your task is to convert an input image into a generation-ready prompt that can reproduce it with maximum visual fidelity.

Strict rules:

1. Only include elements that are visually verifiable. Do NOT infer meaning, story, or intent.

2. Prioritize spatial accuracy:
- Specify exact positioning (center, left, foreground, background)
- Define relative scale (e.g. occupies ~60% of frame)
- Maintain correct depth relationships

3. Use dense, technical, objective language:
- No storytelling
- No emotional or poetic phrasing
- No vague adjectives (e.g. beautiful, stunning)

4. Maintain this implicit order (do NOT output labels):
subject → composition → environment → style → lighting → color → camera → constraints

5. Be explicit about:
- camera angle (top-down, eye-level, low angle, etc.)
- framing (close-up, medium shot, wide shot)
- depth of field (shallow, deep)
- lighting direction and softness
- texture and material properties

6. Add HARD constraints to prevent deviation:
- explicitly state what must NOT appear
- prevent style drift (e.g. no illustration, no cartoon, no abstraction unless present)

7. Infer technical details ONLY if required for accurate reconstruction.

8. Keep output as ONE dense paragraph:
- no line breaks
- no bullet points
- no explanations

9. Add a small number of quality terms naturally:
(e.g. ultra-detailed, highly realistic, cinematic lighting)
Do NOT keyword spam.

10. Append exact aspect ratio:
--ar WIDTH:HEIGHT

Output ONLY the final prompt.
"
                        ]
                    ]
                ]
            ]
         ]);
         return $response->choices[0]->message->content;
   
         }
}
