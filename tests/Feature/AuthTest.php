<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    public function test_login_correcto(): void
    {
        $usuario = User::factory()->create(['username' => 'admin', 'password' => bcrypt('Admin12345!')]);
        $usuario->roles()->attach(Role::where('slug', Role::ADMINISTRADOR)->first());

        $respuesta = $this->post('/login', [
            'username' => 'admin',
            'password' => 'Admin12345!',
        ]);

        $respuesta->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($usuario);
    }

    public function test_login_incorrecto(): void
    {
        User::factory()->create(['username' => 'admin', 'password' => bcrypt('Admin12345!')]);

        $respuesta = $this->post('/login', [
            'username' => 'admin',
            'password' => 'clave-incorrecta',
        ]);

        $respuesta->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_usuario_no_autenticado_es_redirigido_al_login(): void
    {
        $respuesta = $this->get('/dashboard');

        $respuesta->assertRedirect('/login');
    }

    public function test_usuario_inactivo_no_puede_iniciar_sesion(): void
    {
        User::factory()->create([
            'username' => 'inactivo',
            'password' => bcrypt('Clave12345!'),
            'is_active' => false,
        ]);

        $respuesta = $this->post('/login', [
            'username' => 'inactivo',
            'password' => 'Clave12345!',
        ]);

        $respuesta->assertSessionHasErrors('username');
        $this->assertGuest();
    }
}
