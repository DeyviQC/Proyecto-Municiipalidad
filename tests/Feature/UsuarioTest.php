<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsuarioTest extends TestCase
{
    use RefreshDatabase;

    private function crearUsuarioConRol(string $slug): User
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $usuario = User::factory()->create();
        $usuario->roles()->attach(Role::where('slug', $slug)->first());

        return $usuario;
    }

    public function test_asistente_no_puede_gestionar_usuarios(): void
    {
        $asistente = $this->crearUsuarioConRol(Role::ASISTENTE);

        $respuesta = $this->actingAs($asistente)->get('/usuarios');

        $respuesta->assertForbidden();
    }

    public function test_administrador_puede_gestionar_usuarios(): void
    {
        $admin = $this->crearUsuarioConRol(Role::ADMINISTRADOR);

        $respuesta = $this->actingAs($admin)->get('/usuarios');

        $respuesta->assertOk();
    }

    public function test_administrador_puede_crear_usuario(): void
    {
        $admin = $this->crearUsuarioConRol(Role::ADMINISTRADOR);

        $respuesta = $this->actingAs($admin)->post('/usuarios', [
            'name' => 'Nuevo Asistente',
            'username' => 'nuevo.asistente',
            'email' => 'nuevo@municipalidad.local',
            'password' => 'Clave12345!',
            'password_confirmation' => 'Clave12345!',
            'role' => 'asistente',
        ]);

        $respuesta->assertRedirect(route('usuarios.index'));
        $this->assertDatabaseHas('users', ['username' => 'nuevo.asistente']);
    }

    public function test_asistente_no_puede_acceder_a_auditoria(): void
    {
        $asistente = $this->crearUsuarioConRol(Role::ASISTENTE);

        $respuesta = $this->actingAs($asistente)->get('/auditoria');

        $respuesta->assertForbidden();
    }
}
