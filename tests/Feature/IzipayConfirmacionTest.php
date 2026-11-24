<?php
namespace Tests\Feature;

use App\Models\Acta;
use App\Models\PagoTramite;
use App\Models\TramitePublico;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class IzipayConfirmacionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['izipay.mode' => 'test', 'izipay.username' => '12345678', 'izipay.test_password' => 'test-password',
            'izipay.server' => 'https://api.micuentaweb.pe', 'izipay.js_url' => 'https://static.micuentaweb.pe/test.js',
            'izipay.public_test_key' => '12345678:testpublickey_example', 'izipay.hmac_test_key' => 'test-hmac']);
    }

    public function test_confirma_una_sola_vez_y_rechaza_importe_alterado_aunque_la_firma_sea_valida(): void
    {
        $tramite = TramitePublico::create(['codigo' => (string) Str::uuid(), 'acta_id' => Acta::factory()->create()->id,
            'datos' => [], 'email' => 'prueba@example.test', 'terminos_version' => 'test', 'aceptado_at' => now()]);
        $pago = PagoTramite::create(['tramite_publico_id' => $tramite->id, 'proveedor' => 'IZIPAY', 'importe_centimos' => 1030, 'idempotencia' => (string) Str::uuid()]);
        $answer = ['shopId' => '12345678', 'orderCycle' => 'CLOSED', 'orderStatus' => 'PAID',
            'orderDetails' => ['orderId' => $pago->idempotencia, 'orderTotalAmount' => 1030, 'orderCurrency' => 'PEN', 'mode' => 'TEST']];
        $signed = function ($a) { $raw = json_encode($a); return ['kr-answer' => $raw, 'kr-hash-algorithm' => 'sha256_hmac', 'kr-hash' => hash_hmac('sha256', $raw, 'test-password')]; };
        $bad = $answer; $bad['orderDetails']['orderTotalAmount'] = 1;
        $this->post('/pagos/izipay/notificacion', $signed($bad))->assertStatus(422);
        $this->assertNotSame('PAGADO', $pago->fresh()->estado);
        // The real browser return includes slash-containing type names. Signed
        // whitespace must survive Laravel's global input middleware as well.
        $browserAnswer = $answer + ['_type' => 'V4/Payment'];
        $raw = ' '.json_encode($browserAnswer).' ';
        $this->post('/pagos/izipay/retorno', ['kr-answer' => $raw, 'kr-hash-algorithm' => 'sha256_hmac',
            'kr-hash' => hash_hmac('sha256', str_replace('\\/', '/', $raw), 'test-hmac')])->assertRedirect(route('portal.seguimiento'))->assertSessionHas('pago_confirmado', true);
        $this->get('/tramite/seguimiento')->assertOk()->assertSee('¡Tu pago fue aprobado!');
        $this->post('/pagos/izipay/notificacion', $signed($answer))->assertOk();
        $this->post('/pagos/izipay/notificacion', $signed($answer))->assertOk();
        $this->assertSame('PAGADO', $pago->fresh()->estado);
        $this->assertSame('PENDIENTE', $tramite->fresh()->estado);
        $this->assertSame(1, $tramite->eventos()->count());
        $this->post('/pagos/izipay/notificacion', ['kr-answer' => json_encode($answer), 'kr-hash' => str_repeat('0', 64), 'kr-hash-algorithm' => 'sha256_hmac'])->assertUnauthorized();
    }

    public function test_no_anuncia_pago_aprobado_para_una_respuesta_unpaid(): void
    {
        $tramite = TramitePublico::create(['codigo' => (string) Str::uuid(), 'acta_id' => Acta::factory()->create()->id,
            'datos' => [], 'email' => 'prueba@example.test', 'terminos_version' => 'test', 'aceptado_at' => now()]);
        $pago = PagoTramite::create(['tramite_publico_id' => $tramite->id, 'proveedor' => 'IZIPAY', 'importe_centimos' => 1030, 'idempotencia' => (string) Str::uuid()]);
        $raw = json_encode(['shopId' => '12345678', 'orderCycle' => 'CLOSED', 'orderStatus' => 'UNPAID',
            'orderDetails' => ['orderId' => $pago->idempotencia, 'orderTotalAmount' => 1030, 'orderCurrency' => 'PEN', 'mode' => 'TEST']]);
        $this->post('/pagos/izipay/retorno', ['kr-answer' => $raw, 'kr-hash-algorithm' => 'sha256_hmac',
            'kr-hash' => hash_hmac('sha256', $raw, 'test-hmac')])->assertRedirect(route('portal.seguimiento'))->assertSessionMissing('pago_confirmado');
        $this->assertNotSame('PAGADO', $pago->fresh()->estado);
        $this->get('/tramite/seguimiento')->assertDontSee('¡Tu pago fue aprobado!');
    }
}
