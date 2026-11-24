<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Solicitud extends Model
{
    protected $table = 'solicitudes';
    protected $fillable = ['codigo', 'usuario_id', 'tipo', 'titular', 'dni_titular', 'fecha_acta', 'detalle', 'estado', 'version'];

    public const ESTADOS = ['PRESENTADA' => 'Presentada', 'EN_REVISION' => 'En revisión', 'OBSERVADA' => 'Observada', 'APROBADA' => 'Aprobada', 'RECHAZADA' => 'Rechazada'];
    public const TRANSICIONES = ['PRESENTADA' => ['EN_REVISION'], 'EN_REVISION' => ['OBSERVADA', 'APROBADA', 'RECHAZADA'], 'OBSERVADA' => [], 'APROBADA' => [], 'RECHAZADA' => []];

    protected function casts(): array
    {
        return ['fecha_acta' => 'date', 'version' => 'integer'];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function eventos(): HasMany
    {
        return $this->hasMany(SolicitudEvento::class)->orderBy('id');
    }
}
