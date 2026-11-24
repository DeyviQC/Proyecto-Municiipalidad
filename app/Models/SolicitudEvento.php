<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudEvento extends Model
{
    public $timestamps = false;
    protected $fillable = ['usuario_id', 'estado', 'mensaje', 'datos', 'created_at'];
    protected function casts(): array { return ['datos' => 'array', 'created_at' => 'datetime']; }
    public function usuario(): BelongsTo { return $this->belongsTo(User::class, 'usuario_id'); }
}
