<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clientes_deudores', function (Blueprint $table) {
            $table->id();

            // Datos Personales Obligatorios
            $table->string('dni', 10)->unique();
            $table->string('cuil_cuit', 13)->nullable();
            $table->string('apellido', 100);
            $table->string('nombre', 100);
            $table->date('fecha_nacimiento')->nullable();

            // Contacto Obligatorio
            $table->string('telefono_principal', 20);
            $table->string('telefono_secundario', 20)->nullable();
            $table->string('email', 100);

            // Domicilio Legal Obligatorio
            $table->string('domicilio_calle');
            $table->string('domicilio_numero', 10);
            $table->string('domicilio_piso', 10)->nullable();
            $table->string('domicilio_depto', 10)->nullable();
            $table->string('localidad', 100);
            $table->string('codigo_postal', 10)->nullable();
            $table->string('provincia', 50);

            // Datos Adicionales
            $table->enum('estado_civil', ['soltero', 'casado', 'divorciado', 'viudo', 'concubinato'])->nullable();
            $table->string('profesion', 100)->nullable();
            $table->string('lugar_trabajo')->nullable();
            $table->string('telefono_trabajo', 20)->nullable();
            $table->decimal('ingresos_mensuales', 12, 2)->nullable();

            // Referencia Personal
            $table->string('referencia_nombre', 100)->nullable();
            $table->string('referencia_telefono', 20)->nullable();
            $table->string('referencia_relacion', 50)->nullable();

            // Control Crediticio
            $table->decimal('limite_credito', 12, 2)->default(50000.00);
            $table->enum('calificacion_crediticia', ['A', 'B', 'C', 'D'])->default('B');
            $table->text('observaciones')->nullable();

            // Control de Estado
            $table->enum('estado', ['activo', 'suspendido', 'bloqueado', 'inactivo'])->default('activo');
            $table->string('motivo_suspension')->nullable();

            // Auditoria
            $table->foreignId('usuario_registro_id')->constrained('users');

            $table->timestamps();

            // Índices
            $table->index('dni');
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes_deudores');
    }
};
