<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AgendamentoController;

Route::get('/', [AgendamentoController::class, 'index'])->name('agendamento.index');
Route::get('/horarios-disponiveis', [AgendamentoController::class, 'getHorariosDisponiveis'])->name('agendamento.horarios');
Route::post('/agendar', [AgendamentoController::class, 'store'])->name('agendamento.store');
