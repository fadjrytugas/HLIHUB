<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DashboardLoginTest extends TestCase
{
    use DatabaseTransactions;

    public function test_users_table_only_contains_fields_needed_for_the_single_dashboard_account(): void
    {
        $this->assertFalse(Schema::hasColumn('users', 'email'));
        $this->assertFalse(Schema::hasColumn('users', 'email_verified_at'));
        $this->assertFalse(Schema::hasColumn('users', 'remember_token'));
    }

    public function test_dashboard_redirects_guests_to_the_opening_login_page(): void
    {
        $this->withSession(['dashboard_authenticated' => false])
            ->get('/')
            ->assertRedirect(route('login'));

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Selamat datang')
            ->assertSee('images/logo-hli-hub-modern.png')
            ->assertSee('aria-expanded="false"', false)
            ->assertSee('aria-controls="loginPanel"', false)
            ->assertSeeInOrder([
                'id="loginPanel"',
                'aria-labelledby="loginPanelTitle"',
                'hidden',
            ], false);
    }

    public function test_invalid_credentials_do_not_create_an_authenticated_session(): void
    {
        $this->configureTestCredentials();

        $this->withSession(['dashboard_authenticated' => false])
            ->from(route('login'))
            ->post(route('login.store'), [
                'username' => config('services.dashboard_login.username'),
                'password' => 'wrong-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('username')
            ->assertSessionHas('dashboard_authenticated', false);
    }

    public function test_valid_login_and_logout_protect_the_dashboard(): void
    {
        $this->configureTestCredentials();

        $this->withSession(['dashboard_authenticated' => false])
            ->post(route('login.store'), [
                'username' => config('services.dashboard_login.username'),
                'password' => 'test-password',
            ])
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('dashboard_authenticated', true);

        $this->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->get('/')
            ->assertRedirect(route('login'));
    }

    public function test_legacy_dashboard_credentials_create_a_database_user_on_first_login(): void
    {
        config([
            'services.dashboard_login.username' => 'legacy-first-login',
            'services.dashboard_login.password_hash' => Hash::make('test-password'),
        ]);

        $this->withSession(['dashboard_authenticated' => false])
            ->post(route('login.store'), [
                'username' => 'legacy-first-login',
                'password' => 'test-password',
            ])
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('dashboard_authenticated', true)
            ->assertSessionHas('dashboard_user_id');

        $this->assertDatabaseHas('users', [
            'username' => 'legacy-first-login',
            'name' => 'legacy-first-login',
        ]);
    }

    public function test_password_can_be_changed_and_new_password_is_used_for_future_logins(): void
    {
        $user = $this->configureTestCredentials();
        $this->withSession(['dashboard_authenticated' => true, 'dashboard_user_id' => $user->id])
            ->get(route('settings.index'))
            ->assertOk()
            ->assertSee('Ubah Password')
            ->assertSee('themeToggle')
            ->assertSee('Pengaturan SLA')
            ->assertSee('aria-label="Pengaturan"', false)
            ->assertSeeInOrder(['sidebar-settings-area', 'aria-label="Pengaturan"', 'sidebar-logout'], false)
            ->assertDontSee('>Pengaturan</span>', false)
            ->assertDontSee('>Tracking Kegiatan</a>', false);

        $this->from(route('settings.index'))
            ->put(route('settings.password.update'), [
                'current_password' => 'incorrect-password',
                'password' => 'new-test-password',
                'password_confirmation' => 'new-test-password',
            ])
            ->assertRedirect(route('settings.index'))
            ->assertSessionHasErrors('current_password');

        $this->withSession(['dashboard_authenticated' => true, 'dashboard_user_id' => $user->id])
            ->put(route('settings.password.update'), [
                'current_password' => 'test-password',
                'password' => 'new-test-password',
                'password_confirmation' => 'new-test-password',
            ])
            ->assertRedirect(route('settings.index'))
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('new-test-password', $user->fresh()->password));

        $this->post(route('logout'));
        $this->withSession(['dashboard_authenticated' => false])
            ->post(route('login.store'), [
                'username' => $user->username,
                'password' => 'new-test-password',
            ])
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('dashboard_authenticated', true);
    }

    private function configureTestCredentials(): User
    {
        $username = 'dashboard-test-'.bin2hex(random_bytes(6));
        config([
            'services.dashboard_login.username' => $username,
            'services.dashboard_login.password_hash' => Hash::make('test-password'),
        ]);

        return User::factory()->create([
            'name' => 'Dashboard Admin',
            'username' => $username,
            'password' => 'test-password',
        ]);
    }
}
