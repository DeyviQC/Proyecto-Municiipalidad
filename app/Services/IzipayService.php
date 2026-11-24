<?php

namespace App\Services;

/** Mi Cuenta Web REST V4; distinct from the Punto Web SDK. */
class IzipayService
{
    public function disponible(): bool
    {
        return config('izipay.mode') === 'test'
            && str_contains((string) config('izipay.public_test_key'), ':testpublickey_')
            && collect(['username', 'test_password', 'server', 'js_url', 'public_test_key', 'hmac_test_key'])
                ->every(fn ($key) => is_string(config('izipay.'.$key)) && trim(config('izipay.'.$key)) !== '');
    }

    public function verificarFirma(array $respuesta, bool $ipn = false): bool
    {
        return $this->errorFirma($respuesta, $ipn) === null;
    }

    public function errorFirma(array $respuesta, bool $ipn = false): ?string
    {
        if (!$this->disponible()) return 'CONFIGURACION_INCOMPLETA';
        if (!isset($respuesta['kr-answer'], $respuesta['kr-hash'])) return 'RETORNO_INCOMPLETO';
        if (($respuesta['kr-hash-algorithm'] ?? '') !== 'sha256_hmac') return 'ALGORITMO_NO_VALIDO';
        $answer = $respuesta['kr-answer'] ?? null;
        $hash = $respuesta['kr-hash'] ?? null;
        if (!is_string($answer) || strlen($answer) > 100000 || !is_string($hash) || !preg_match('/^[a-f0-9]{64}$/i', $hash)) return 'FORMATO_NO_VALIDO';
        // Browser returns use the HMAC key; server-to-server IPN uses the REST password.
        $key = config($ipn ? 'izipay.test_password' : 'izipay.hmac_test_key');
        // Official Mi Cuenta Web PHP integration normalizes escaped JSON slashes
        // before HMAC calculation. Do not decode/re-encode or alter other bytes.
        $answer = str_replace('\\/', '/', $answer);
        return hash_equals(hash_hmac('sha256', $answer, $key), strtolower($hash)) ? null : 'FIRMA_NO_COINCIDE';
    }
}
