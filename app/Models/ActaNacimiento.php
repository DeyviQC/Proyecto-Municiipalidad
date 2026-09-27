<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActaNacimiento extends Model
{
    use HasFactory;

    protected $table = 'acta_nacimientos';

    protected $fillable = [
        'acta_id',
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'dni',
        'fecha_nacimiento',
        'hora_nacimiento',
        'lugar_nacimiento',
        'sexo',
        'nombre_padre',
        'dni_padre',
        'nombre_madre',
        'dni_madre',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
        ];
    }

    public function acta(): BelongsTo
    {
        return $this->belongsTo(Acta::class);
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombres} {$this->apellido_paterno} {$this->apellido_materno}");
    }
}
