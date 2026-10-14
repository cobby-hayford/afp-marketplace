<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\User;
use App\Notifications\QueuedResetPassword;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    public function test_user_can_register_and_legal_acceptances_are_recorded(): void
    {
        $country = Country::where('iso_code', 'GH')->firstOrFail();
        $response = $this->post('/register', [
            'first_name' => 'Ama', 'last_name' => 'Mensah', 'email' => 'ama@example.test',
            'password' => 'StrongPass123', 'password_confirmation' => 'StrongPass123',
            'country_id' => $country->id, 'account_type' => 'producer',
            'terms' => true, 'privacy' => true, 'guidelines' => true,
        ]);

        $response->assertRedirect(route('verification.notice'));
        $user = User::where('email', 'ama@example.test')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->terms_accepted_at);
        $this->assertNotNull($user->privacy_accepted_at);
        $this->assertNotNull($user->guidelines_accepted_at);
        $this->assertDatabaseHas('profiles', ['user_id' => $user->id, 'status' => 'draft']);
    }

    public function test_unverified_user_cannot_enter_approved_portal(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);
        $this->actingAs($user)->get('/dashboard')->assertRedirect(route('verification.notice'));
    }

    public function test_email_verification_works(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);
        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(30), [
            'id' => $user->id, 'hash' => sha1($user->email),
        ]);
        $this->actingAs($user)->get($url)->assertRedirect(route('profile.edit'));
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_user_can_login(): void
    {
        $user = $this->approvedUser();
        $this->post('/login', ['email' => $user->email, 'password' => 'Password1234'])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_password_reset_link_can_be_requested(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $this->post('/forgot-password', ['email' => $user->email])->assertSessionHasNoErrors();
        Notification::assertSentTo($user, QueuedResetPassword::class);
    }
}
