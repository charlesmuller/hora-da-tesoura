# Hora da Tesoura

Sistema de agendamento para barbearia desenvolvido com Laravel + Filament.

**Stack:** Laravel + Filament + Docker + Nginx + MySQL

## 🎯 Funcionalidades

### 👥 Interface Pública
- Seleção de barbeiro (2 barbeiros disponíveis)
- Escolha de data (segunda a sábado)
- Seleção de horários disponíveis:
  - **Manhã:** 09:00 às 11:30 (intervalos de 30min)
  - **Tarde:** 14:00 às 19:00 (intervalos de 30min)
- Formulário de dados do cliente (nome + telefone)
- Confirmação em tempo real

### 🔧 Painel Administrativo
- Gestão completa de barbeiros
- Visualização e edição de agendamentos
- Controle de status (agendado/cancelado/finalizado)
- Dashboard organizado

## 🚀 Requisitos
- Docker + Docker Compose
- Git

## 🛠️ Instalação e Uso

### Primeiro uso (local)
```bash
chmod +x scripts/init-local.sh
./scripts/init-local.sh
```

### Corrigir permissões (se necessário)
```bash
chmod +x scripts/fix-permissions.sh
./scripts/fix-permissions.sh
```

## 🌐 Acessos
- **Público:** http://localhost:8080
- **Admin:** http://localhost:8080/admin
  - Email: charlesmuller@rede.ulbra.br
  - Senha: (definida durante instalação)

## 🗄️ Banco de Dados
O sistema está configurado para usar MySQL em produção. As migrations criam:

- **barbeiros:** id, nome, descrição, ativo, timestamps
- **agendamentos:** id, barbeiro_id, cliente_nome, cliente_telefone, data_agendamento, hora_agendamento, status, observações, timestamps

### Seeders inclusos:
- 2 barbeiros de exemplo
- Usuário admin para o Filament

## 🔧 Resolução de Problemas

### Erro de permissão nos logs
Se aparecer erro de permissão no `laravel.log`, execute:
```bash
./scripts/fix-permissions.sh
```

### Reset do banco
```bash
php artisan migrate:fresh --seed
```

## 📱 Notificações WhatsApp

O sistema suporta notificações automáticas via WhatsApp quando um agendamento é realizado.

### 🔧 Configuração

1. **Configure as variáveis no `.env`:**
```bash
WHATSAPP_NOTIFICATIONS_ENABLED=true
WHATSAPP_BASE_URL=https://your-evolution-api.com
WHATSAPP_API_TOKEN=your_api_token_here
WHATSAPP_INSTANCE=default
WHATSAPP_ADMIN_PHONE_1=5511999999999
WHATSAPP_ADMIN_PHONE_2=5511888888888
```

2. **APIs suportadas:**
   - Evolution API (recomendada)
   - Twilio (com pequenos ajustes)
   - Outras APIs compatíveis

### 📨 Tipos de notificação

- **Para administradores:** Novo agendamento realizado
- **Para cliente:** Confirmação do agendamento
- **Lembretes:** (pode ser implementado com cron jobs)

### 🧪 Testar notificações
```bash
php artisan whatsapp:test 5511999999999
```

### 📋 Mensagens enviadas

**Para administradores:**
- Detalhes completos do agendamento
- Nome e telefone do cliente
- Link para o painel administrativo

**Para clientes:**
- Confirmação do agendamento
- Dados do barbeiro e horário
- Informações da barbearia