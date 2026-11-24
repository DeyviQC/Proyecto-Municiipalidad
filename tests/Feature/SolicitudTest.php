<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Solicitud;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SolicitudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    private function usuario(string $rol): User
    {
        $user = User::factory()->create(['username' => 'prueba_'.\Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(12))]);
        $user->roles()->attach(Role::where('slug', $rol)->firstOrFail());
        return $user;
    }

    private function datos(): array
    {
        return ['tipo' => 'NACIMIENTO', 'titular' => 'Persona Ficticia', 'dni_titular' => '12345678', 'detalle' => 'Solicito copia de mi acta para una prueba.'];
    }

    private function presentar(User $user): Solicitud
    {
        $this->actingAs($user)->post('/ciudadano/solicitudes', $this->datos())->assertSessionHasNoErrors()->assertRedirect();
        return Solicitud::latest('id')->firstOrFail();
    }

    public function test_registro_publico_no_permite_elegir_rol_y_restringe_el_archivo_interno(): void
    {
        $this->get('/ciudadano/registro')->assertRedirect('/tramite');
        $this->post('/ciudadano/registro', [])->assertRedirect('/tramite');
        $user = $this->usuario('ciudadano');
        $this->actingAs($user);
        $this->assertTrue($user->isCiudadano());
        $this->assertTrue($user->is_active);
        $this->assertFalse($user->isAdmin());
        foreach (['/dashboard', '/actas', '/reportes', '/usuarios', '/auditoria', '/solicitudes', '/api/actas'] as $ruta) $this->get($ruta)->assertForbidden();
        $this->get('/ciudadano/solicitudes')->assertOk()->assertDontSee('Registrar acta');
    }

    public function test_solo_el_propietario_puede_consultar_su_solicitud(): void
    {
        $user = $this->usuario('ciudadano');
        $solicitud = $this->presentar($user);
        $this->get('/ciudadano/solicitudes/'.$solicitud->id)->assertOk();
        $otro = $this->usuario('ciudadano');
        $this->actingAs($otro)->get('/ciudadano/solicitudes')->assertOk()->assertDontSee('Persona Ficticia');
        $this->get('/ciudadano/solicitudes/'.$solicitud->id)->assertForbidden();
        $this->patch('/solicitudes/'.$solicitud->id.'/estado', ['estado' => 'APROBADA', 'version' => 1, 'mensaje' => 'No permitido'])->assertForbidden();
    }

    public function test_revision_observacion_y_subsanacion_conservan_historial_y_rechazan_versiones_antiguas(): void
    {
        $user = $this->usuario('ciudadano');
        $solicitud = $this->presentar($user);
        $asistente = $this->usuario('asistente');
        $ruta = '/solicitudes/'.$solicitud->id.'/estado';
        $this->actingAs($asistente)->get('/solicitudes')->assertOk();
        $this->get('/solicitudes/'.$solicitud->id)->assertOk();
        $this->patch($ruta, ['estado' => 'APROBADA', 'version' => 1, 'mensaje' => 'Salto inválido'])->assertSessionHasErrors('estado');
        $this->patch($ruta, ['estado' => 'EN_REVISION', 'version' => 1, 'mensaje' => 'Revisión iniciada'])->assertSessionHasNoErrors();
        $this->patch($ruta, ['estado' => 'OBSERVADA', 'version' => 1, 'mensaje' => 'Versión antigua'])->assertSessionHasErrors('version');
        $this->patch($ruta, ['estado' => 'OBSERVADA', 'version' => 2, 'mensaje' => 'Indique la fecha de inscripción.'])->assertSessionHasNoErrors();
        $this->actingAs($user)->get('/ciudadano/solicitudes/'.$solicitud->id)->assertOk()->assertSee('Subsanar observaciones');
        $this->patch('/ciudadano/solicitudes/'.$solicitud->id, $this->datos() + ['version' => 3, 'fecha_acta' => '2020-01-01', 'usuario_id' => $asistente->id, 'estado' => 'APROBADA'])->assertSessionHasNoErrors();
        $solicitud->refresh();
        $this->assertSame('PRESENTADA', $solicitud->estado);
        $this->assertSame($user->id, $solicitud->usuario_id);
        $this->assertCount(4, $solicitud->eventos);
        $this->assertSame('2020-01-01', $solicitud->eventos->last()->datos['fecha_acta']);
        $this->assertDatabaseHas('auditoria', ['accion' => 'SUBSANAR_SOLICITUD', 'registro_id' => $solicitud->id]);
    }

    public function test_aprobacion_cierra_transiciones_y_no_entrega_pdf(): void
    {
        $solicitud = $this->presentar($this->usuario('ciudadano'));
        $this->actingAs($this->usuario('administrador'));
        $ruta = '/solicitudes/'.$solicitud->id.'/estado';
        $this->patch($ruta, ['estado' => 'EN_REVISION', 'version' => 1, 'mensaje' => 'Revisando'])->assertSessionHasNoErrors();
        $this->patch($ruta, ['estado' => 'APROBADA', 'version' => 2, 'mensaje' => 'Requisitos revisados'])->assertSessionHasNoErrors();
        $this->get('/solicitudes/'.$solicitud->id)->assertOk()->assertSee('La aprobación no equivale');
        $this->patch($ruta, ['estado' => 'RECHAZADA', 'version' => 3, 'mensaje' => 'Reapertura'])->assertSessionHasErrors('estado');
        $this->assertSame('APROBADA', $solicitud->fresh()->estado);
    }

    public function test_login_ciudadano_y_solicitud_invalida(): void
    {
        $user = $this->usuario('ciudadano');
        $user->update(['password' => 'ClavePrueba123!']);
        $this->post('/login', ['username' => $user->username, 'password' => 'ClavePrueba123!'])->assertRedirect('/ciudadano/solicitudes');
        $this->post('/ciudadano/solicitudes', ['tipo' => 'OTRO', 'titular' => 'Prueba', 'dni_titular' => '123'])->assertSessionHasErrors(['tipo', 'dni_titular', 'detalle']);
        $this->assertDatabaseCount('solicitudes', 0);
    }

    public function test_usuario_desactivado_no_puede_presentar_y_visitante_necesita_sesion(): void
    {
        $this->get('/ciudadano/solicitudes')->assertRedirect('/login');
        $user = $this->usuario('ciudadano');
        $user->update(['is_active' => false]);
        $this->actingAs($user)->post('/ciudadano/solicitudes', $this->datos())->assertRedirect('/login');
        $this->assertDatabaseCount('solicitudes', 0);
    }

    public function test_administrador_puede_editar_ciudadano_sin_cambiar_su_rol(): void
    {
        $user = $this->usuario('ciudadano');
        $admin = $this->usuario('administrador');
        $this->actingAs($admin)->put('/usuarios/'.$user->id, ['name' => 'Ciudadano Actualizado', 'username' => $user->username, 'email' => $user->email, 'role' => 'ciudadano', 'is_active' => 1])->assertSessionHasNoErrors()->assertRedirect('/usuarios');
        $this->assertTrue($user->fresh()->isCiudadano());
    }
}
