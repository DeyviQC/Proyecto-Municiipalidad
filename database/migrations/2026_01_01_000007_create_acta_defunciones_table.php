<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acta_defunciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('acta_id')->constrained('actas')->cascadeOnDelete();

            $table->string('nombres', 100);
            $table->string('apellido_paterno', 80);
            $table->string('apellido_materno', 80);
            $table->string('dni', 15)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->date('fecha_defuncion');
            $table->string('lugar_defuncion', 150);
            $table->string('causa_defuncion', 200);
            $table->string('medico_certifica', 150)->nullable();

            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index('dni');
            $table->index(['apellido_paterno', 'apellido_materno']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acta_defunciones');
    }
};
