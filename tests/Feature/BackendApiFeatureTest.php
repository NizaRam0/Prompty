<?php

use App\Models\PromptGeneration;
use App\Models\User;
use App\Services\OpenAiService;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\Sanctum;

test('guest cannot access protected api routes', function () {
    $this->getJson('/api/v1/user/1')->assertStatus(401);
    $this->postJson('/api/v1/prompt-generations')->assertStatus(401);
});
//test connected to database and can create user

test ('db connection test', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'name' => 'Test User',
    ]);
    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'name' => 'Test User',
    ]);

    // Check storage (bucket) access
    \Storage::fake('public');
    $filePath = 'test-bucket-file.txt';
    $fileContents = 'bucket test';
    \Storage::disk('public')->put($filePath, $fileContents);
    $this->assertTrue(\Storage::disk('public')->exists($filePath));
    $this->assertEquals($fileContents, \Storage::disk('public')->get($filePath));
});

test('register creates user and returns no content', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'Strong#Pass123',
        'password_confirmation' => 'Strong#Pass123',
    ]);

    $response->assertNoContent();

    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'name' => 'Test User',
    ]);
});

test('register requires unique email', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $response = $this->postJson('/api/register', [
        'name' => 'Another User',
        'email' => 'taken@example.com',
        'password' => 'Strong#Pass123',
        'password_confirmation' => 'Strong#Pass123',
    ]);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('login returns user and token', function () {
    $user = User::factory()->create([
        'email' => 'login@example.com',
        'password' => bcrypt('password'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'login@example.com',
        'password' => 'password',
    ]);

    $response
        ->assertOk()
        ->assertJsonStructure([
            'user' => ['id', 'name', 'email'],
            'token',
        ]);

    $this->assertDatabaseCount('personal_access_tokens', 1);
    $this->assertEquals($user->id, $response->json('user.id'));
});

test('login with invalid credentials returns validation error', function () {
    $user = User::factory()->create([
        'email' => 'login@example.com',
        'password' => bcrypt('password'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('forgot password sends reset notification', function () {
    Notification::fake();

    $user = User::factory()->create();

    $response = $this->postJson('/api/forgot-password', [
        'email' => $user->email,
    ]);

    $response->assertOk()->assertJsonStructure(['status']);

    Notification::assertSentTo($user, ResetPassword::class);
});

test('password can be reset with valid token', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->postJson('/api/forgot-password', ['email' => $user->email])->assertOk();

    Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user) {
        $response = $this->postJson('/api/reset-password', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

        $response->assertOk()->assertJsonStructure(['status']);

        return true;
    });
});

test('unverified user can request verification email', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/email/verification-notification');

    $response
        ->assertOk()
        ->assertJson([
            'status' => 'verification-link-sent',
        ]);
});

test('verified user requesting verification email is redirected', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->post('/api/email/verification-notification');

    $response->assertRedirect('/dashboard');
});

test('email can be verified with valid signed url', function () {
    Event::fake();

    $user = User::factory()->unverified()->create();
    Sanctum::actingAs($user);

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $response = $this->get($verificationUrl);

    $response->assertRedirect(config('app.frontend_url').'/dashboard?verified=1');
    $this->assertTrue($user->fresh()->hasVerifiedEmail());
    Event::assertDispatched(Verified::class);
});

test('authenticated user endpoint returns current user', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->getJson("/api/v1/user/{$user->id}");

    $response
        ->assertOk()
        ->assertJson([
            'data' => [
                'id' => $user->id,
                'email' => $user->email,
            ],
        ]);
});

test('prompt generation store uploads image and saves record', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->mock(OpenAIService::class, function ($mock) {
        $mock->shouldReceive('generatePromptForImage')
            ->once()
            ->andReturn('Generated prompt from mock service.');
    });

    $file = UploadedFile::fake()->image('sample.jpg', 300, 300)->size(512);

    $response = $this->postJson('/api/v1/prompt-generations', [
        'image' => $file,
    ]);

    $response
        ->assertStatus(201)
        ->assertJsonStructure([
            'data' => [
                'id',
                'image_url',
                'generated_prompt',
                'original_file_name',
                'file_size',
                'mime_type',
                'created_at',
                'updated_at',
            ],
        ])
        ->assertJsonPath('data.generated_prompt', 'Generated prompt from mock service.');

    $record = PromptGeneration::first();

    $this->assertNotNull($record);
    Storage::disk('public')->assertExists($record->image_path);

    $this->assertDatabaseHas('prompt_generations', [
        'id' => $record->id,
        'user_id' => $user->id,
        'generated_prompt' => 'Generated prompt from mock service.',
        'original_file_name' => 'sample.jpg',
    ]);
});

test('prompt generation store requires image file', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/prompt-generations', []);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['image']);
});

test('prompt generation store rejects invalid mime type', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $invalidFile = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

    $response = $this->postJson('/api/v1/prompt-generations', [
        'image' => $invalidFile,
    ]);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['image']);
});

test('prompt generation store rejects oversized file', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $largeFile = UploadedFile::fake()->image('too-large.jpg', 1200, 1200)->size(11264);

    $response = $this->postJson('/api/v1/prompt-generations', [
        'image' => $largeFile,
    ]);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['image']);
});

test('prompt generation store rejects small dimensions', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $smallImage = UploadedFile::fake()->image('small.jpg', 50, 50)->size(200);

    $response = $this->postJson('/api/v1/prompt-generations', [
        'image' => $smallImage,
    ]);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['image']);
});

test('prompt generation index returns only authenticated users records with filters', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();

    PromptGeneration::create([
        'generated_prompt' => 'Sunset beach scene',
        'image_path' => 'uploads/images/owner-1.jpg',
        'original_file_name' => 'owner-1.jpg',
        'file_size' => 1234,
        'mime_type' => 'image/jpeg',
        'user_id' => $owner->id,
    ]);

    PromptGeneration::create([
        'generated_prompt' => 'Minimalist logo',
        'image_path' => 'uploads/images/owner-2.png',
        'original_file_name' => 'owner-2.png',
        'file_size' => 2000,
        'mime_type' => 'image/png',
        'user_id' => $owner->id,
    ]);

    PromptGeneration::create([
        'generated_prompt' => 'Other user prompt',
        'image_path' => 'uploads/images/other.jpg',
        'original_file_name' => 'other.jpg',
        'file_size' => 1500,
        'mime_type' => 'image/jpeg',
        'user_id' => $otherUser->id,
    ]);

    Sanctum::actingAs($owner);

    $response = $this->getJson('/api/v1/prompt-generations?search=Sunset&mime_type=image/jpeg&sort=created_at&per_page=10');

    $response
        ->assertOk()
        ->assertJsonStructure([
            'data',
            'links',
            'meta',
        ]);

    $data = $response->json('data');

    $this->assertCount(1, $data);
    $this->assertEquals('Sunset beach scene', $data[0]['generated_prompt']);
    $this->assertEquals('image/jpeg', $data[0]['mime_type']);
});
