<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Acta extends Model
{
    use HasFactory;

    public const TIPO_NACIMIENTO = 'NACIMIENTO';
    public const TIPO_MATRIMONIO = 'MATRIMONIO';
    public const TIPO_DEFUNCION = 'DEFUNCION';

    public const ESTADO_REGISTRADA = 'REGISTRADA';
    public const ESTADO_ANULADA = 'ANULADA';

    protected $table = 'actas';

    protected $fillable = [
        'numero_acta',
        'anio',
        'tipo',
        'fecha_acta',
        'observaciones',
        'usuario_id',
        'estado',
        'pdf_original',
        'pdf_ruta',
        'pdf_hash',
        'pdf_tamano',
    ];

    protected function casts(): array
    {
        return [
            'fecha_acta' => 'date',
            'anio' => 'integer',
            'pdf_tamano' => 'integer',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function nacimiento(): HasOne
    {
        return $this->hasOne(ActaNacimiento::class);
    }

    public function matrimonio(): HasOne
    {
        return $this->hasOne(ActaMatrimonio::class);
    }

    public function defuncion(): HasOne
    {
        return $this->hasOne(ActaDefuncion::class);
    }

    /**
     * Devuelve el detalle específico según el tipo de acta.
     */
    public function detalle(): ActaNacimiento|ActaMatrimonio|ActaDefuncion|null
    {
        return match ($this->tipo) {
            self::TIPO_NACIMIENTO => $this->nacimiento,
            self::TIPO_MATRIMONIO => $this->matrimonio,
            self::TIPO_DEFUNCION => $this->defuncion,
            default => null,
        };
    }

    /**
     * Código público del acta, ej: ACT-000125
     */
    public function getCodigoAttribute(): string
    {
        return 'ACT-' . str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    public function estaBloqueada(): bool
    {
        return $this->estado === self::ESTADO_REGISTRADA;
    }
}
