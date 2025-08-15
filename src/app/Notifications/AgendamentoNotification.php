<?php

namespace App\Notifications;

use App\Models\Agendamento;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AgendamentoNotification
{
    private $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Notificar novo agendamento para administradores
     */
    public function notifyNewBooking(Agendamento $agendamento): bool
    {
        if (!config('whatsapp.enabled') || !$this->whatsappService->isConfigured()) {
            Log::info('WhatsApp notifications disabled or not configured');
            return false;
        }

        $message = $this->buildNewBookingMessage($agendamento);
        $adminPhones = array_filter(config('whatsapp.admin_phones'));
        
        $success = true;
        foreach ($adminPhones as $phone) {
            if (!empty($phone)) {
                $sent = $this->whatsappService->sendMessage($phone, $message);
                if (!$sent) {
                    $success = false;
                }
            }
        }

        return $success;
    }

    /**
     * Confirmar agendamento para o cliente
     */
    public function confirmBooking(Agendamento $agendamento): bool
    {
        if (!config('whatsapp.enabled') || !$this->whatsappService->isConfigured()) {
            return false;
        }

        $message = $this->buildBookingConfirmationMessage($agendamento);
        return $this->whatsappService->sendMessage($agendamento->cliente_telefone, $message);
    }

    /**
     * Lembrete de agendamento (para ser usado em comando agendado)
     */
    public function sendReminder(Agendamento $agendamento): bool
    {
        if (!config('whatsapp.enabled') || !$this->whatsappService->isConfigured()) {
            return false;
        }

        $message = $this->buildReminderMessage($agendamento);
        return $this->whatsappService->sendMessage($agendamento->cliente_telefone, $message);
    }

    /**
     * Construir mensagem de novo agendamento para admins
     */
    private function buildNewBookingMessage(Agendamento $agendamento): string
    {
        $template = config('whatsapp.templates.new_booking');
        
        $observations = $agendamento->observacoes 
            ? "📝 *Observações:* " . $agendamento->observacoes 
            : "";

        $adminUrl = config('app.url') . '/admin/agendamentos';

        return str_replace([
            ':date',
            ':time',
            ':barber',
            ':client_name',
            ':client_phone',
            ':observations',
            ':admin_url'
        ], [
            $this->formatDate($agendamento->data_agendamento),
            $this->formatTime($agendamento->hora_agendamento),
            $agendamento->barbeiro->nome,
            $agendamento->cliente_nome,
            $agendamento->cliente_telefone,
            $observations,
            $adminUrl
        ], $template);
    }

    /**
     * Construir mensagem de confirmação para cliente
     */
    private function buildBookingConfirmationMessage(Agendamento $agendamento): string
    {
        $template = config('whatsapp.templates.booking_confirmation');

        return str_replace([
            ':client_name',
            ':date',
            ':time',
            ':barber'
        ], [
            $agendamento->cliente_nome,
            $this->formatDate($agendamento->data_agendamento),
            $this->formatTime($agendamento->hora_agendamento),
            $agendamento->barbeiro->nome
        ], $template);
    }

    /**
     * Construir mensagem de lembrete
     */
    private function buildReminderMessage(Agendamento $agendamento): string
    {
        $template = config('whatsapp.templates.booking_reminder');

        return str_replace([
            ':client_name',
            ':date',
            ':time',
            ':barber'
        ], [
            $agendamento->cliente_nome,
            $this->formatDate($agendamento->data_agendamento),
            $this->formatTime($agendamento->hora_agendamento),
            $agendamento->barbeiro->nome
        ], $template);
    }

    /**
     * Formatar data para exibição
     */
    private function formatDate($date): string
    {
        return Carbon::parse($date)->locale('pt_BR')->format('d/m/Y (l)');
    }

    /**
     * Formatar horário para exibição
     */
    private function formatTime($time): string
    {
        return Carbon::parse($time)->format('H:i');
    }

    /**
     * Testar envio de mensagem
     */
    public function testMessage(string $phoneNumber): array
    {
        $testMessage = "🧪 *Teste de Notificação - Hora da Tesoura*\n\n" .
                      "Esta é uma mensagem de teste do sistema de notificações.\n\n" .
                      "✅ Se você recebeu esta mensagem, as notificações estão funcionando corretamente!\n\n" .
                      "_Enviado em: " . now()->format('d/m/Y H:i') . "_";

        $success = $this->whatsappService->sendMessage($phoneNumber, $testMessage);
        
        return [
            'success' => $success,
            'message' => $success 
                ? 'Mensagem de teste enviada com sucesso!' 
                : 'Falha ao enviar mensagem de teste.',
            'phone' => $phoneNumber
        ];
    }
}
