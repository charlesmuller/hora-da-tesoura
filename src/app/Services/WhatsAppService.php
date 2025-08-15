<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private $client;
    private $baseUrl;
    private $apiToken;
    private $instance;

    public function __construct()
    {
        $this->client = new Client();
        $this->baseUrl = config('whatsapp.base_url');
        $this->apiToken = config('whatsapp.api_token');
        $this->instance = config('whatsapp.instance');
    }

    /**
     * Enviar mensagem de texto via WhatsApp
     */
    public function sendMessage(string $phoneNumber, string $message): bool
    {
        try {
            // Formatar número de telefone (remover caracteres especiais)
            $cleanPhone = $this->formatPhoneNumber($phoneNumber);
            
            // Preparar dados da mensagem
            $data = [
                'number' => $cleanPhone,
                'textMessage' => [
                    'text' => $message
                ]
            ];

            // Headers para autenticação
            $headers = [
                'Content-Type' => 'application/json',
                'apikey' => $this->apiToken,
            ];

            // URL completa da API
            $url = $this->baseUrl . '/message/sendText/' . $this->instance;

            // Fazer requisição
            $response = $this->client->post($url, [
                'headers' => $headers,
                'json' => $data,
                'timeout' => 30,
            ]);

            $statusCode = $response->getStatusCode();
            $responseBody = json_decode($response->getBody(), true);

            if ($statusCode === 200 || $statusCode === 201) {
                Log::info('WhatsApp message sent successfully', [
                    'phone' => $cleanPhone,
                    'response' => $responseBody
                ]);
                return true;
            }

            Log::error('Failed to send WhatsApp message', [
                'phone' => $cleanPhone,
                'status_code' => $statusCode,
                'response' => $responseBody
            ]);

            return false;

        } catch (RequestException $e) {
            Log::error('WhatsApp API request failed', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage(),
                'response' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('Unexpected error sending WhatsApp message', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Formatar número de telefone
     */
    private function formatPhoneNumber(string $phone): string
    {
        // Remove todos os caracteres não numéricos
        $cleanPhone = preg_replace('/\D/', '', $phone);
        
        // Se começar com 0, remove o 0
        if (substr($cleanPhone, 0, 1) === '0') {
            $cleanPhone = substr($cleanPhone, 1);
        }
        
        // Se não começar com 55 (código do Brasil), adiciona
        if (!str_starts_with($cleanPhone, '55')) {
            $cleanPhone = '55' . $cleanPhone;
        }
        
        return $cleanPhone;
    }

    /**
     * Verificar se o serviço está configurado
     */
    public function isConfigured(): bool
    {
        return !empty($this->baseUrl) && 
               !empty($this->apiToken) && 
               !empty($this->instance);
    }

    /**
     * Testar conexão com a API
     */
    public function testConnection(): array
    {
        try {
            if (!$this->isConfigured()) {
                return [
                    'success' => false,
                    'message' => 'WhatsApp service not configured'
                ];
            }

            $headers = [
                'apikey' => $this->apiToken,
            ];

            $url = $this->baseUrl . '/instance/status/' . $this->instance;

            $response = $this->client->get($url, [
                'headers' => $headers,
                'timeout' => 10,
            ]);

            if ($response->getStatusCode() === 200) {
                return [
                    'success' => true,
                    'message' => 'WhatsApp service is connected',
                    'data' => json_decode($response->getBody(), true)
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to connect to WhatsApp service'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage()
            ];
        }
    }
}
