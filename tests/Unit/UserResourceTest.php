<?php

use App\Http\Resources\UserResource;
use App\Models\PromptGeneration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('user resource includes expected attributes and prompt generation count', function () {
    $user = User::factory()->create();

    PromptGeneration::create([
        'generated_prompt' => 'Prompt one',
        'image_path' => 'uploads/images/one.jpg',
        'original_file_name' => 'one.jpg',
        'file_size' => 1234,
        'mime_type' => 'image/jpeg',
        'user_id' => $user->id,
    ]);

    PromptGeneration::create([
        'generated_prompt' => 'Prompt two',
        'image_path' => 'uploads/images/two.png',
        'original_file_name' => 'two.png',
        'file_size' => 4321,
        'mime_type' => 'image/png',
        'user_id' => $user->id,
    ]);

    $resource = new UserResource($user->fresh());
    $payload = $resource->toArray(new Request());

    expect($payload)->toHaveKeys([
        'id',
        'name',
        'email',
        'number_of_prompts_generated',
        'created_at',
        'updated_at',
    ]);

    expect($payload['id'])->toBe($user->id);
    expect($payload['email'])->toBe($user->email);
    expect($payload['number_of_prompts_generated'])->toBe(2);
    expect($payload['created_at'])->toMatch('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/');
    expect($payload['updated_at'])->toMatch('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/');
});
