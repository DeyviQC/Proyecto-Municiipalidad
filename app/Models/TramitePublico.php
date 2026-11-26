<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TramitePublico extends Model
{
    protected $table = 'tramites_publicos';
    protected $guarded = ['id'];
    protected $hidden = ['datos', 'email', 'otp_hash', 'dni_hash'];

    /** Keyed hash of a DNI (blind index): exact, indexed lookups without storing the DNI in clear text. */
    public static function hashDni(?string $dni): ?string
    {
        return $dni && preg_match('/^[0-9]{8}$/D', $dni) ? hash_hmac('sha256', 'dni:'.$dni, (string) config('app.key')) : null;
    }

    protected static function booted(): void
    {
        static::saving(function (TramitePublico $tramite) {
            if ($tramite->isDirty('datos')) $tramite->dni_hash = self::hashDni($tramite->datos['dni'] ?? null);
        });
    }
    public const RELACIONES = ['titular' => 'Soy el titular del acta', 'madre' => 'Soy la madre', 'padre' => 'Soy el padre', 'representante' => 'Soy representante', 'otro' => 'Otra relación'];

    public function requiereRevisionVinculo(): bool
    {
        // A declaration is never proof. Legacy requests with no relation also require review.
        if (($this->datos['relacion'] ?? null) !== 'titular') return true;
        $fecha = $this->acta?->tipo === 'NACIMIENTO' ? $this->acta->nacimiento?->fecha_nacimiento : null;
        return $this->acta?->tipo === 'NACIMIENTO' && (!$fecha || $fecha->gt(now()->subYears(18)));
    }
    public const ESTADOS = ['PENDIENTE_PAGO' => 'Pendiente de pago', 'PENDIENTE' => 'Pendiente de atención', 'EN_REVISION' => 'En revisión', 'OBSERVADA' => 'Observada', 'APROBADA' => 'Aprobada, pendiente de firma', 'LISTA' => 'Documento listo para descargar', 'RECHAZADA' => 'Rechazada', 'VENCIDA' => 'Vencida (no se realizó el pago)'];
    public const TRANSICIONES = ['PENDIENTE' => ['EN_REVISION'], 'EN_REVISION' => ['OBSERVADA', 'APROBADA', 'RECHAZADA'], 'OBSERVADA' => ['EN_REVISION'], 'APROBADA' => [], 'RECHAZADA' => [], 'VENCIDA' => []];
    protected function casts(): array
    {
        return ['datos' => 'encrypted:array', 'aceptado_at' => 'datetime', 'otp_expira_at' => 'datetime', 'otp_enviado_at' => 'datetime', 'email_verificado_at' => 'datetime'];
    }
    public function acta() { return $this->belongsTo(Acta::class); }
    public function pago() { return $this->hasOne(PagoTramite::class); }
    public function eventos() { return $this->hasMany(TramiteEvento::class)->orderBy('id'); }
}
