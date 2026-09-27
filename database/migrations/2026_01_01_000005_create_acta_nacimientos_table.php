<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acta_nacimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('acta_id')->constrained('actas')->cascadeOnDelete();

            $table->string('nombres', 100);
            $table->string('apellido_paterno', 80);
            $table->string('apellido_materno', 80);
            $table->string('dni', 15)->nullable();
            $table->date('fecha_nacimiento');
            $table->time('hora_nacimiento')->nullable();
            $table->string('lugar_nacimiento', 150);
            $table->enum('sexo', ['M', 'F']);

            $table->string('nombre_padre', 150)->nullable();
            $table->string('dni_padre', 15)->nullable();
            $table->string('nombre_madre', 150)->nullable();
            $table->string('dni_madre', 15)->nullable();

            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index('dni');
            $table->index(['apellido_paterno', 'apellido_materno']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acta_nacimientos');
    }
};
