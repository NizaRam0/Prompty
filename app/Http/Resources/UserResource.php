<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\RateLimiter;

class UserResource extends JsonResource
{
    private const DAILY_PROMPT_LIMIT = 5;

    private const UNLIMITED_EMAILS = [
        'nizar@gmail.com',
        'elnizarramadan61@gmail.com',
    ];

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $quota = $this->generationQuota($request);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'number_of_prompts_generated' => $this->PromptGenerations()->count(),
            'daily_generation_limit' => $quota['limit'],
            'daily_generation_remaining' => $quota['remaining'],
            'daily_generation_used' => $quota['used'],
            'daily_generation_unlimited' => $quota['unlimited'],
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get the authenticated user's daily generation quota information.
     *
     * @return array{limit:int|null, remaining:int|null, used:int|null, unlimited:bool}
     */
    private function generationQuota(Request $request): array
    {
        $user = $request->user();
        $key = $user?->id ?: $request->ip();

        if ($user && in_array($user->email, self::UNLIMITED_EMAILS, true)) {
            return [
                'limit' => null,
                'remaining' => null,
                'used' => null,
                'unlimited' => true,
            ];
        }

        $remaining = RateLimiter::remaining((string) $key, self::DAILY_PROMPT_LIMIT);

        return [
            'limit' => self::DAILY_PROMPT_LIMIT,
            'remaining' => $remaining,
            'used' => self::DAILY_PROMPT_LIMIT - $remaining,
            'unlimited' => false,
        ];
    }
}
