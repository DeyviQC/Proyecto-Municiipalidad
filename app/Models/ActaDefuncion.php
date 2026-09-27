<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActaDefuncion extends Model
{
    use HasFactory;

    protected $table = 'acta_defunciones';

    protected $fillable = [
        'acta_id',
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'dni',
        'fecha_nacimiento',
        'fecha_defuncion',
        'lugar_defuncion',
        'causa_defuncion',
        'medico_certifica',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'fecha_defuncion' => 'date',
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
