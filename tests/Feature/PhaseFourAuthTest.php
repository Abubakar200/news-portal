<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class PhaseFourAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_signs_in_and_sends_an_email_verification_notification(): void
    {
        Notification::fake();

        $this->get(route('register', ['locale' => 'en']))->assertOk();

        $response = $this->post(route('register.store', ['locale' => 'en']), [
            'name' => 'New Reader',
            'email' => 'reader@example.test',
            'password' => 'StrongPass123!',
            'password_confirmation' => 'StrongPass123!',
        ]);
        $user = User::query()->where('email', 'reader@example.test')->firstOrFail();

        $response->assertRedirect(route('verification.notice', ['locale' => 'en']));
        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Hash::check('StrongPass123!', $user->password));
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_forgot_password_sends_a_reset_link_without_revealing_unknown_accounts(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->get(route('password.request', ['locale' => 'ur']))->assertOk();

        $this->post(route('password.email', ['locale' => 'en']), [
            'email' => $user->email,
        ])->assertRedirect()
            ->assertSessionHas('status', 'If the address belongs to an account, password reset instructions will be sent.');

        Notification::assertSentTo($user, ResetPassword::class);

        $this->post(route('password.email', ['locale' => 'en']), [
            'email' => 'missing@example.test',
        ])->assertRedirect()
            ->assertSessionHas('status', 'If the address belongs to an account, password reset instructions will be sent.');
    }

    public function test_login_and_logout_regenerate_the_authenticated_session(): void
    {
        $user = User::factory()->create([
            'password' => 'StrongPass123!',
        ]);

        $this->get(route('login', ['locale' => 'en']))->assertOk();

        $this->post(route('login.store', ['locale' => 'en']), [
            'email' => $user->email,
            'password' => 'StrongPass123!',
        ])->assertRedirect(route('site.home', ['locale' => 'en']));

        $this->assertAuthenticatedAs($user);

        $this->post(route('logout', ['locale' => 'en']))
            ->assertRedirect(route('site.home', ['locale' => 'en']));

        $this->assertGuest();
    }

    public function test_password_reset_accepts_a_valid_token_and_hashes_the_new_password(): void
    {
        $user = User::factory()->create([
            'password' => 'OldStrong123!',
        ]);
        $token = Password::createToken($user);

        $this->get(route('password.reset', [
            'locale' => 'en',
            'token' => $token,
            'email' => $user->email,
        ]))->assertOk();

        $this->post(route('password.update', ['locale' => 'en']), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewStrong123!',
            'password_confirmation' => 'NewStrong123!',
        ])->assertRedirect(route('login', ['locale' => 'en']))
            ->assertSessionHas('status', 'Your password has been reset.');

        $this->assertTrue(Hash::check('NewStrong123!', $user->fresh()->password));
    }

    public function test_signed_email_verification_marks_the_authenticated_user_verified(): void
    {
        $user = User::factory()->unverified()->create();
        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(30), [
            'locale' => 'ur',
            'id' => $user->getKey(),
            'hash' => sha1($user->getEmailForVerification()),
        ]);

        $this->actingAs($user)
            ->get($url)
            ->assertRedirect(route('site.home', ['locale' => 'ur']));

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_profile_edit_saves_a_photo_and_requires_verification_after_email_change(): void
    {
        Storage::fake('public');
        Notification::fake();
        $user = User::factory()->create([
            'email' => 'old@example.test',
            'email_verified_at' => now(),
        ]);
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/r7kAAAAASUVORK5CYII=', true);

        $this->actingAs($user)
            ->patch(route('profile.update', ['locale' => 'en']), [
                'name' => 'Updated Reader',
                'email' => 'new@example.test',
                'bio' => '<script>alert(1)</script>',
                'profile_photo' => UploadedFile::fake()->createWithContent('avatar.png', $png),
            ])
            ->assertRedirect(route('verification.notice', ['locale' => 'en']));

        $user->refresh();
        $this->assertSame('Updated Reader', $user->name);
        $this->assertSame('new@example.test', $user->email);
        $this->assertNull($user->email_verified_at);
        $this->assertSame('<script>alert(1)</script>', $user->bio);
        Storage::disk('public')->assertExists($user->profile_photo_path);
        Notification::assertSentTo($user, VerifyEmail::class);

        $this->get(route('profile.edit', ['locale' => 'en']))
            ->assertOk()
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_password_change_requires_the_current_password_and_hashes_the_new_one(): void
    {
        $user = User::factory()->create([
            'password' => 'CurrentStrong123!',
        ]);

        $this->actingAs($user)
            ->patch(route('profile.password', ['locale' => 'en']), [
                'current_password' => 'WrongPassword123!',
                'password' => 'NewStrong123!',
                'password_confirmation' => 'NewStrong123!',
            ])
            ->assertSessionHasErrors('current_password');

        $this->patch(route('profile.password', ['locale' => 'en']), [
            'current_password' => 'CurrentStrong123!',
            'password' => 'NewStrong123!',
            'password_confirmation' => 'NewStrong123!',
        ])->assertRedirect();

        $this->assertTrue(Hash::check('NewStrong123!', $user->fresh()->password));
    }
}
