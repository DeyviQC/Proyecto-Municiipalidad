<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla central de actas. Una vez creado un registro aquí
     * (estado = REGISTRADA) el registro se considera inmutable:
     * no se actualizan sus columnas principales ni se elimina.
     * Ver App\Policies\ActaPolicy.
     */
    public function up(): void
    {
        Schema::create('actas', function (Blueprint $table) {
            $table->id();
            $table->string('numero_acta', 20);
            $table->unsignedSmallInteger('anio');
            $table->enum('tipo', ['NACIMIENTO', 'MATRIMONIO', 'DEFUNCION']);
            $table->date('fecha_acta');
            $table->text('observaciones')->nullable();
            $table->foreignId('usuario_id')->constrained('users');
            $table->enum('estado', ['REGISTRADA', 'ANULADA'])->default('REGISTRADA');

            // Documento
            $table->string('pdf_original'); // nombre original subido por el usuario
            $table->string('pdf_ruta');     // ruta física segura en storage/app/private
            $table->char('pdf_hash', 64);   // SHA-256
            $table->unsignedBigInteger('pdf_tamano'); // bytes

            $table->timestamps();

            // Evita duplicidad de acta por número+año+tipo
            $table->unique(['numero_acta', 'anio', 'tipo'], 'actas_numero_anio_tipo_unique');

            // Evita duplicidad de documento físico
            $table->unique('pdf_hash', 'actas_pdf_hash_unique');

            $table->index('numero_acta');
            $table->index('anio');
            $table->index('tipo');
            $table->index('fecha_acta');
            $table->index('usuario_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actas');
    }
};
