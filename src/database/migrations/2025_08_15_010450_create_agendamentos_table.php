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
        Schema::create('agendamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barbeiro_id')->constrained('barbeiros')->onDelete('cascade');
            $table->string('cliente_nome');
            $table->string('cliente_telefone');
            $table->date('data_agendamento');
            $table->time('hora_agendamento');
            $table->enum('status', ['agendado', 'cancelado', 'finalizado'])->default('agendado');
            $table->text('observacoes')->nullable();
            $table->timestamps();
            
            // Índice único para evitar duplo agendamento no mesmo horário para o mesmo barbeiro
            $table->unique(['barbeiro_id', 'data_agendamento', 'hora_agendamento'], 'unique_agendamento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agendamentos');
    }
};
