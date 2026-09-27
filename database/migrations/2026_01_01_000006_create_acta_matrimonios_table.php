<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acta_matrimonios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('acta_id')->constrained('actas')->cascadeOnDelete();

            // Contrayente 1
            $table->string('c1_nombres', 100);
            $table->string('c1_apellido_paterno', 80);
            $table->string('c1_apellido_materno', 80);
            $table->string('c1_dni', 15)->nullable();
            $table->date('c1_fecha_nacimiento');

            // Contrayente 2
            $table->string('c2_nombres', 100);
            $table->string('c2_apellido_paterno', 80);
            $table->string('c2_apellido_materno', 80);
            $table->string('c2_dni', 15)->nullable();
            $table->date('c2_fecha_nacimiento');

            $table->date('fecha_matrimonio');
            $table->string('lugar_matrimonio', 150);
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index('c1_dni');
            $table->index('c2_dni');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acta_matrimonios');
    }
};
