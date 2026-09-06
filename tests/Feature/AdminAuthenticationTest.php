<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use RuntimeException;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_and_guest_redirect(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/login')->assertInertia(fn (Assert $page) => $page->component('Admin/Login'));
    }

    public function test_admin_can_login_and_logout(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'password'])->assertRedirect('/admin');
        $this->assertAuthenticatedAs($admin);
        $this->post('/admin/logout')->assertRedirect('/admin/login');
        $this->assertGuest();
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_regular_user_and_incorrect_password_cannot_login(): void
    {
        $user = User::factory()->create();
        $this->post('/admin/login', ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors(['email' => 'Las credenciales no permiten acceder al panel.']);
        $this->assertGuest();
        $admin = User::factory()->create(['is_admin' => true]);
        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'incorrect'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_is_rate_limited_after_five_failures(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        for ($i = 0; $i < 5; $i++) {
            $this->post('/admin/login', ['email' => $admin->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        }
        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'password'])->assertSessionHasErrors(['email' => 'Demasiados intentos. Intenta nuevamente en un minuto.']);
        $this->assertGuest();
    }

    public function test_permission_is_reserved_for_admins(): void
    {
        $regular = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);
        $this->assertFalse(Gate::forUser($regular)->allows('manage-admin'));
        $this->assertTrue(Gate::forUser($admin)->allows('manage-admin'));
        $this->actingAs($regular)->get('/admin')->assertForbidden();
    }

    public function test_command_creates_admin_without_exposing_password(): void
    {
        $this->artisan('admin:create', ['email' => 'admin@example.test', '--name' => 'Administración'])
            ->expectsQuestion('Contraseña (mínimo 12 caracteres)', 'segura-y-privada-123')
            ->expectsQuestion('Repite la contraseña', 'segura-y-privada-123')
            ->expectsOutput('Administrador creado. Ingresa en /admin.')->assertSuccessful();
        $user = User::where('email', 'admin@example.test')->firstOrFail();
        $this->assertTrue($user->is_admin);
        $this->assertTrue(Hash::check('segura-y-privada-123', $user->password));
    }

    public function test_command_rejects_weak_password_without_creating_user(): void
    {
        $this->artisan('admin:create', ['email' => 'admin@example.test'])
            ->expectsQuestion('Contraseña (mínimo 12 caracteres)', 'short')
            ->expectsQuestion('Repite la contraseña', 'short')
            ->assertFailed();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_command_resets_the_password_of_an_existing_account(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'email' => 'admin@example.test']);

        $this->artisan('admin:password', ['email' => 'admin@example.test'])
            ->expectsQuestion('Contraseña nueva (mínimo 12 caracteres)', 'otra-clave-larga-456')
            ->expectsQuestion('Repite la contraseña', 'otra-clave-larga-456')
            ->expectsOutput('Contraseña actualizada para admin@example.test.')->assertSuccessful();

        $this->assertTrue(Hash::check('otra-clave-larga-456', $admin->refresh()->password));
        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'otra-clave-larga-456'])
            ->assertRedirect('/admin');
        $this->assertAuthenticatedAs($admin);
    }

    public function test_password_reset_rejects_a_weak_password_and_an_unknown_account(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'email' => 'admin@example.test']);
        $original = $admin->password;

        $this->artisan('admin:password', ['email' => 'admin@example.test'])
            ->expectsQuestion('Contraseña nueva (mínimo 12 caracteres)', 'corta')
            ->expectsQuestion('Repite la contraseña', 'corta')
            ->assertFailed();
        $this->assertSame($original, $admin->refresh()->password);

        $this->artisan('admin:password', ['email' => 'nadie@example.test'])
            ->expectsOutput('No existe una cuenta con el correo nadie@example.test.')->assertFailed();
    }

    public function test_the_seeder_leaves_a_working_local_administrator(): void
    {
        $this->seed(AdminUserSeeder::class);

        $admin = User::where('email', 'marcelonicolasespana@gmail.com')->firstOrFail();
        $this->assertTrue($admin->is_admin);

        $this->post('/admin/login', ['email' => $admin->email, 'password' => '123456'])
            ->assertRedirect('/admin');
        $this->assertAuthenticatedAs($admin);
    }

    /**
     * Sembrar dos veces no puede fallar por correo duplicado, y deja la
     * contraseña conocida aunque la fila estuviera con un valor sin hashear.
     */
    public function test_the_seeder_repairs_an_account_whose_password_is_not_a_hash(): void
    {
        $this->seed(AdminUserSeeder::class);
        DB::table('users')->update(['password' => '123', 'is_admin' => false]);

        $this->seed(AdminUserSeeder::class);

        $admin = User::where('email', 'marcelonicolasespana@gmail.com')->firstOrFail();
        $this->assertDatabaseCount('users', 1);
        $this->assertTrue($admin->is_admin);
        $this->assertTrue(Hash::check('123456', $admin->password));
    }

    /**
     * Una contraseña guardada sin hashear —por un INSERT a mano, por ejemplo—
     * no puede dar acceso jamás. bcrypt rechaza el valor en vez de compararlo,
     * de modo que el fallo se nota en lugar de pasar por "clave incorrecta".
     */
    public function test_a_password_stored_unhashed_can_never_authenticate(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        DB::table('users')->where('id', $admin->id)->update(['password' => '123']);

        $this->expectException(RuntimeException::class);

        Auth::attempt(['email' => $admin->email, 'password' => '123']);
    }
}
