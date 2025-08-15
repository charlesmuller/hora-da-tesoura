<?php

namespace App\Http\Controllers;

use App\Models\Barbeiro;
use App\Models\Agendamento;
use App\Services\WhatsAppService;
use App\Notifications\AgendamentoNotification;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AgendamentoController extends Controller
{
    public function index()
    {
        $barbeiros = Barbeiro::ativos()->get();
        return view('agendamento.index', compact('barbeiros'));
    }

    public function getHorariosDisponiveis(Request $request)
    {
        $barbeiro_id = $request->get('barbeiro_id');
        $data = $request->get('data');
        
        if (!$barbeiro_id || !$data) {
            return response()->json(['error' => 'Parâmetros inválidos'], 400);
        }

        // Verificar se a data é válida (não pode ser anterior a hoje)
        $dataAgendamento = Carbon::parse($data);
        if ($dataAgendamento->isPast()) {
            return response()->json(['error' => 'Data inválida'], 400);
        }

        // Verificar se é segunda a sábado
        if ($dataAgendamento->isSunday()) {
            return response()->json(['error' => 'Não atendemos aos domingos'], 400);
        }

        // Horários disponíveis
        $horariosManha = [
            '09:00', '09:30', '10:00', '10:30', '11:00', '11:30'
        ];
        
        $horariosTarde = [
            '14:00', '14:30', '15:00', '15:30', '16:00', '16:30', '17:00', '17:30', '18:00', '18:30', '19:00'
        ];

        $todosHorarios = array_merge($horariosManha, $horariosTarde);

        // Buscar agendamentos já existentes nesta data para este barbeiro
        $agendamentosExistentes = Agendamento::where('barbeiro_id', $barbeiro_id)
            ->where('data_agendamento', $data)
            ->where('status', 'agendado')
            ->pluck('hora_agendamento')
            ->map(function($hora) {
                return Carbon::parse($hora)->format('H:i');
            })
            ->toArray();

        // Filtrar horários disponíveis
        $horariosDisponiveis = array_diff($todosHorarios, $agendamentosExistentes);

        return response()->json([
            'horarios_manha' => array_intersect($horariosManha, $horariosDisponiveis),
            'horarios_tarde' => array_intersect($horariosTarde, $horariosDisponiveis)
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'barbeiro_id' => 'required|exists:barbeiros,id',
            'cliente_nome' => 'required|string|max:255',
            'cliente_telefone' => 'required|string|max:20',
            'data_agendamento' => 'required|date|after_or_equal:today',
            'hora_agendamento' => 'required|date_format:H:i',
            'observacoes' => 'nullable|string|max:500'
        ]);

        // Verificar se o horário ainda está disponível
        $agendamentoExistente = Agendamento::where('barbeiro_id', $request->barbeiro_id)
            ->where('data_agendamento', $request->data_agendamento)
            ->where('hora_agendamento', $request->hora_agendamento)
            ->where('status', 'agendado')
            ->exists();

        if ($agendamentoExistente) {
            return response()->json(['error' => 'Horário já ocupado'], 409);
        }

        // Criar agendamento
        $agendamento = Agendamento::create([
            'barbeiro_id' => $request->barbeiro_id,
            'cliente_nome' => $request->cliente_nome,
            'cliente_telefone' => $request->cliente_telefone,
            'data_agendamento' => $request->data_agendamento,
            'hora_agendamento' => $request->hora_agendamento,
            'observacoes' => $request->observacoes,
            'status' => 'agendado'
        ]);

        // Carregar relacionamento do barbeiro
        $agendamento->load('barbeiro');

        // Enviar notificações WhatsApp
        try {
            $whatsappService = new WhatsAppService();
            $notification = new AgendamentoNotification($whatsappService);
            
            // Notificar admins sobre novo agendamento
            $notification->notifyNewBooking($agendamento);
            
            // Confirmar agendamento para o cliente
            $notification->confirmBooking($agendamento);
            
        } catch (\Exception $e) {
            // Log do erro mas não falha o agendamento
            \Log::error('Erro ao enviar notificações WhatsApp', [
                'agendamento_id' => $agendamento->id,
                'error' => $e->getMessage()
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Agendamento realizado com sucesso!',
            'agendamento' => $agendamento
        ]);
    }
}
