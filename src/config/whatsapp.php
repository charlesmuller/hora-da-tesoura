<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WhatsApp API Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your WhatsApp API settings here. 
    | This configuration supports Evolution API, but can be adapted for other providers.
    |
    */

    'base_url' => env('WHATSAPP_BASE_URL', 'https://your-evolution-api.com'),
    'api_token' => env('WHATSAPP_API_TOKEN', ''),
    'instance' => env('WHATSAPP_INSTANCE', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    */

    'enabled' => env('WHATSAPP_NOTIFICATIONS_ENABLED', false),
    
    /*
    |--------------------------------------------------------------------------
    | Admin Phone Numbers
    |--------------------------------------------------------------------------
    |
    | Phone numbers that will receive admin notifications
    |
    */
    
    'admin_phones' => [
        env('WHATSAPP_ADMIN_PHONE_1', ''),
        env('WHATSAPP_ADMIN_PHONE_2', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Message Templates
    |--------------------------------------------------------------------------
    */

    'templates' => [
        'new_booking' => "🔔 *Novo Agendamento - Hora da Tesoura*\n\n" .
                        "📅 *Data:* :date\n" .
                        "🕐 *Horário:* :time\n" .
                        "💇 *Barbeiro:* :barber\n" .
                        "👤 *Cliente:* :client_name\n" .
                        "📱 *Telefone:* :client_phone\n\n" .
                        ":observations\n\n" .
                        "Acesse o painel admin para mais detalhes: :admin_url",

        'booking_confirmation' => "✅ *Agendamento Confirmado - Hora da Tesoura*\n\n" .
                                 "Olá *:client_name*!\n\n" .
                                 "Seu agendamento foi confirmado:\n\n" .
                                 "📅 *Data:* :date\n" .
                                 "🕐 *Horário:* :time\n" .
                                 "💇 *Barbeiro:* :barber\n\n" .
                                 "📍 *Endereço:* [Seu endereço aqui]\n\n" .
                                 "Agradecemos a preferência! 😊",

        'booking_reminder' => "⏰ *Lembrete - Hora da Tesoura*\n\n" .
                             "Olá *:client_name*!\n\n" .
                             "Lembramos que você tem um agendamento:\n\n" .
                             "📅 *Amanhã* - :date\n" .
                             "🕐 *Horário:* :time\n" .
                             "💇 *Barbeiro:* :barber\n\n" .
                             "Até lá! 👋",
    ],
];
