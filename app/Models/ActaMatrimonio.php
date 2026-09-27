<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActaMatrimonio extends Model
{
    use HasFactory;

    protected $table = 'acta_matrimonios';

    protected $fillable = [
        'acta_id',
        'c1_nombres',
        'c1_apellido_paterno',
        'c1_apellido_materno',
        'c1_dni',
        'c1_fecha_nacimiento',
        'c2_nombres',
        'c2_apellido_paterno',
        'c2_apellido_materno',
        'c2_dni',
        'c2_fecha_nacimiento',
        'fecha_matrimonio',
        'lugar_matrimonio',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'c1_fecha_nacimiento' => 'date',
            'c2_fecha_nacimiento' => 'date',
            'fecha_matrimonio' => 'date',
        ];
    }

    public function acta(): BelongsTo
    {
        return $this->belongsTo(Acta::class);
    }

    public function getContrayente1Attribute(): string
    {
        return trim("{$this->c1_nombres} {$this->c1_apellido_paterno} {$this->c1_apellido_materno}");
    }

    public function getContrayente2Attribute(): string
    {
        return trim("{$this->c2_nombres} {$this->c2_apellido_paterno} {$this->c2_apellido_materno}");
    }
}
