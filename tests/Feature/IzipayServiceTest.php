<?php

namespace Tests\Feature;

use App\Models\PagoTramite;
use App\Models\TramitePublico;
use App\Services\IzipayService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IzipayServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['izipay.mode' => 'test', 'izipay.username' => 'test-store', 'izipay.test_password' => 'test-password',
            'izipay.server' => 'https://api.micuentaweb.pe/api-payment/', 'izipay.js_url' => 'https://static.micuentaweb.pe/test.js',
            'izipay.public_test_key' => '12345678:testpublickey_example', 'izipay.hmac_test_key' => 'test-hmac', 'portal.http_ca_file' => null]);
        Http::preventStrayRequests();
    }

    public function test_firma_separa_retorno_de_navegador_e_ipn_y_detecta_alteraciones(): void
    {
        $service = app(IzipayService::class);
        $data = ['kr-answer' => '{"orderStatus":"PAID"}', 'kr-hash-algorithm' => 'sha256_hmac'];
        $data['kr-hash'] = hash_hmac('sha256', $data['kr-answer'], 'test-hmac');
        $this->assertTrue($service->verificarFirma($data));
        $this->assertFalse($service->verificarFirma($data, true));
        $data['kr-hash'] = hash_hmac('sha256', $data['kr-answer'], 'test-password');
        $this->assertTrue($service->verificarFirma($data, true));
        $data['kr-answer'] = '{"orderStatus":"UNPAID"}';
        $this->assertFalse($service->verificarFirma($data, true));
    }

    public function test_no_habilita_produccion_ni_configuracion_incompleta(): void
    {
        config(['izipay.mode' => 'production']);
        $this->assertFalse(app(IzipayService::class)->disponible());
        config(['izipay.mode' => 'test', 'izipay.hmac_test_key' => '']);
        $this->assertFalse(app(IzipayService::class)->disponible());
    }

    public function test_normaliza_solo_barras_json_como_el_ejemplo_oficial(): void
    {
        $answer = '{"url":"https:\\/\\/example.test\\/pago","texto":"á"}';
        $data = ['kr-answer' => $answer, 'kr-hash-algorithm' => 'sha256_hmac',
            'kr-hash' => hash_hmac('sha256', str_replace('\\/', '/', $answer), 'test-hmac')];
        $this->assertTrue(app(IzipayService::class)->verificarFirma($data));
        $data['kr-answer'] = str_replace('pago', 'otro', $answer);
        $this->assertFalse(app(IzipayService::class)->verificarFirma($data));
    }
}
