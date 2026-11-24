<?php

namespace Tests\Feature;

use App\Models\{Acta, PagoTramite, TramitePublico};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class WhatsAppConsultaTest extends TestCase
{
    use RefreshDatabase;

    private function tramite(string $pago): TramitePublico
    {
        Http::preventStrayRequests();
        config(['portal.whatsapp_contacto' => '15555550100']);
        $t = TramitePublico::create(['codigo' => (string) Str::uuid(), 'acta_id' => Acta::factory()->create()->id,
            'email' => 'privado@example.test', 'email_verificado_at' => now(), 'estado' => 'PENDIENTE',
            'datos' => ['nombre' => 'Solicitante privado', 'dni' => '77777777', 'relacion' => 'titular', 'busqueda' => ['tipo' => 'NACIMIENTO', 'titular' => 'Titular privado']],
            'terminos_version' => 'test', 'aceptado_at' => now()]);
        PagoTramite::create(['tramite_publico_id' => $t->id, 'importe_centimos' => 1030, 'estado' => $pago, 'idempotencia' => (string) Str::uuid()]);
        return $t;
    }

    private function sesion(TramitePublico $t): array
    {
        return ['portal_acceso' => ['codigo' => $t->codigo, 'expira' => now()->addMinutes(20)->timestamp]];
    }

    public function test_despues_del_pago_ofrece_chat_solo_con_numero_de_solicitud_y_sin_enviar_nada(): void
    {
        $t = $this->tramite('PAGADO');
        $response = $this->withSession($this->sesion($t))->get(route('portal.estado'))->assertOk()->assertSee('Notificar por WhatsApp');
        $dom = new \DOMDocument;
        @$dom->loadHTML($response->getContent());
        $link = (new \DOMXPath($dom))->query('//a[starts-with(@href,"https://wa.me/")]')->item(0);
        $this->assertNotNull($link);
        $url = $link->getAttribute('href');
        $this->assertSame('/15555550100', parse_url($url, PHP_URL_PATH));
        parse_str(parse_url($url, PHP_URL_QUERY), $query);
        $this->assertSame('Hola, deseo recibir por WhatsApp el documento de mi solicitud N.º '.str_pad((string) $t->id, 6, '0', STR_PAD_LEFT).' de Registro Civil. Ya realicé el pago.', $query['text']);
        foreach ([$t->codigo, '77777777', $t->email, 'Titular privado', 'Solicitante privado', '/tramite/documento'] as $dato) $this->assertStringNotContainsString($dato, urldecode($url));
        $this->assertSame('noopener noreferrer', $link->getAttribute('rel'));
        $this->assertSame('no-referrer', $link->getAttribute('referrerpolicy'));
        $this->assertSame('PENDIENTE', $t->fresh()->estado);
        $this->assertSame(1, $t->fresh()->version);
        $this->assertSame(0, $t->eventos()->count());
        Http::assertNothingSent();
    }

    public function test_no_ofrece_whatsapp_antes_de_confirmar_el_pago_ni_abre_el_seguimiento_sin_verificar(): void
    {
        $t = $this->tramite('CREANDO');
        $this->withSession($this->sesion($t))->get(route('portal.estado'))->assertOk()->assertDontSee('https://wa.me/', false);
        $this->withSession(['portal_acceso' => null])->get(route('portal.estado'))->assertForbidden();
        $this->assertSame(0, $t->eventos()->count());
    }

    public function test_omite_el_boton_si_falta_el_numero_o_el_formato_es_invalido(): void
    {
        $t = $this->tramite('PAGADO');
        foreach ([null, '', 'https://otro.example', '0000', 'abc51999999999', str_repeat('9', 20)] as $numero) {
            config(['portal.whatsapp_contacto' => $numero]);
            $this->withSession($this->sesion($t))->get(route('portal.estado'))->assertOk()->assertDontSee('https://wa.me/', false);
        }
        Http::assertNothingSent();
    }
}

