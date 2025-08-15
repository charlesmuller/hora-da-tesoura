<?php

namespace App\Console\Commands;

use App\Services\WhatsAppService;
use App\Notifications\AgendamentoNotification;
use Illuminate\Console\Command;

class TestWhatsAppCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:test {phone : Phone number to send test message}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test WhatsApp notification system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $phone = $this->argument('phone');
        
        $this->info('🧪 Testing WhatsApp notification system...');
        $this->newLine();

        // Test service configuration
        $whatsappService = new WhatsAppService();
        
        $this->info('📋 Checking configuration...');
        if (!$whatsappService->isConfigured()) {
            $this->error('❌ WhatsApp service is not properly configured!');
            $this->line('Please set the following environment variables:');
            $this->line('- WHATSAPP_BASE_URL');
            $this->line('- WHATSAPP_API_TOKEN');
            $this->line('- WHATSAPP_INSTANCE');
            return Command::FAILURE;
        }
        
        $this->info('✅ Configuration OK');
        $this->newLine();

        // Test connection
        $this->info('🔗 Testing API connection...');
        $connectionTest = $whatsappService->testConnection();
        
        if (!$connectionTest['success']) {
            $this->error('❌ Connection failed: ' . $connectionTest['message']);
            return Command::FAILURE;
        }
        
        $this->info('✅ Connection OK');
        $this->newLine();

        // Send test message
        $this->info("📱 Sending test message to: {$phone}");
        
        $notification = new AgendamentoNotification($whatsappService);
        $result = $notification->testMessage($phone);
        
        if ($result['success']) {
            $this->info('✅ Test message sent successfully!');
            $this->line('Check your WhatsApp to confirm the message was received.');
        } else {
            $this->error('❌ Failed to send test message: ' . $result['message']);
            return Command::FAILURE;
        }

        $this->newLine();
        $this->info('🎉 WhatsApp notification system is working correctly!');
        
        return Command::SUCCESS;
    }
}
