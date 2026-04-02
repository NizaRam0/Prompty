<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

test('authenticated user can view own profile', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->getJson("/api/v1/user/{$user->id}");

    $response
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'email',
                'number_of_prompts_generated',
                'daily_generation_limit',
                'daily_generation_remaining',
                'daily_generation_used',
                'daily_generation_unlimited',
                'created_at',
                'updated_at',
            ],
        ])
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.email', $user->email);
});

test('user cannot view another user profile', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->getJson("/api/v1/user/{$other->id}");

    $response
        ->assertStatus(403)
        ->assertJson([
            'message' => 'Forbidden',
        ]);
});

test('authenticated user can update own profile', function () {
    $user = User::factory()->create([
        'password' => 'Old#Pass123',
    ]);
    Sanctum::actingAs($user);

    $response = $this->patchJson("/api/v1/user/{$user->id}", [
        'name' => 'Updated Name',
        'password' => 'New#Pass123',
        'password_confirmation' => 'New#Pass123',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.name', 'Updated Name');

    $user->refresh();
    expect($user->name)->toBe('Updated Name');
    expect(Hash::check('New#Pass123', $user->password))->toBeTrue();
});

test('user update validates required fields and password confirmation', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->patchJson("/api/v1/user/{$user->id}", [
        'name' => 'A',
        'password' => 'New#Pass123',
    ]);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'password']);
});

test('user cannot update another user profile', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->patchJson("/api/v1/user/{$other->id}", [
        'name' => 'Updated Name',
        'password' => 'New#Pass123',
        'password_confirmation' => 'New#Pass123',
    ]);

    $response
        ->assertStatus(403)
        ->assertJson([
            'message' => 'Forbidden',
        ]);
});

test('authenticated user can delete own account and tokens', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token');
    Sanctum::actingAs($user);

    $response = $this->deleteJson("/api/v1/user/{$user->id}");

    $response->assertNoContent();

    $this->assertDatabaseMissing('users', [
        'id' => $user->id,
    ]);

    $this->assertDatabaseMissing('personal_access_tokens', [
        'id' => $token->accessToken->id,
    ]);
});

test('user cannot delete another user', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->deleteJson("/api/v1/user/{$other->id}");

    $response
        ->assertStatus(403)
        ->assertJson([
            'message' => 'Forbidden',
        ]);
});
