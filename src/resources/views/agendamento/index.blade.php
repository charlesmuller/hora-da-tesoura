<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} - Agendamento</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        .selected {
            @apply bg-blue-600 text-white;
        }
        .available {
            @apply bg-green-100 text-green-800 hover:bg-green-200 cursor-pointer;
        }
        .unavailable {
            @apply bg-gray-100 text-gray-400 cursor-not-allowed;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen py-8" x-data="agendamentoApp()">
        <div class="max-w-2xl mx-auto px-4">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-gray-900 mb-2">{{ config('app.name') }}</h1>
                <p class="text-lg text-gray-600">Agende seu horário com facilidade</p>
            </div>

            <!-- Formulário de Agendamento -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <!-- Etapa 1: Selecionar Barbeiro -->
                <div x-show="step === 1" class="space-y-6">
                    <h2 class="text-2xl font-semibold text-gray-900 mb-4">Escolha seu barbeiro</h2>
                    <div class="grid gap-4">
                        @foreach($barbeiros as $barbeiro)
                        <div class="border rounded-lg p-4 cursor-pointer hover:border-blue-500 transition-colors"
                             :class="selectedBarbeiro === {{ $barbeiro->id }} ? 'border-blue-500 bg-blue-50' : 'border-gray-200'"
                             @click="selectBarbeiro({{ $barbeiro->id }}, '{{ $barbeiro->nome }}')">
                            <h3 class="font-semibold text-lg">{{ $barbeiro->nome }}</h3>
                            @if($barbeiro->descricao)
                                <p class="text-gray-600 mt-1">{{ $barbeiro->descricao }}</p>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    <button @click="nextStep()" 
                            :disabled="!selectedBarbeiro"
                            :class="selectedBarbeiro ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-300 cursor-not-allowed'"
                            class="w-full text-white py-3 px-4 rounded-lg font-semibold transition-colors">
                        Próximo
                    </button>
                </div>

                <!-- Etapa 2: Selecionar Data -->
                <div x-show="step === 2" class="space-y-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-2xl font-semibold text-gray-900">Escolha a data</h2>
                        <button @click="step = 1" class="text-blue-600 hover:text-blue-800">← Voltar</button>
                    </div>
                    <div class="mb-4">
                        <p class="text-gray-600">Barbeiro selecionado: <span class="font-semibold" x-text="selectedBarbeiroNome"></span></p>
                    </div>
                    <input type="date" 
                           x-model="selectedData"
                           :min="new Date().toISOString().split('T')[0]"
                           class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <button @click="loadHorarios()" 
                            :disabled="!selectedData"
                            :class="selectedData ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-300 cursor-not-allowed'"
                            class="w-full text-white py-3 px-4 rounded-lg font-semibold transition-colors">
                        Ver Horários Disponíveis
                    </button>
                </div>

                <!-- Etapa 3: Selecionar Horário -->
                <div x-show="step === 3" class="space-y-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-2xl font-semibold text-gray-900">Escolha o horário</h2>
                        <button @click="step = 2" class="text-blue-600 hover:text-blue-800">← Voltar</button>
                    </div>
                    <div class="mb-4">
                        <p class="text-gray-600">
                            <span class="font-semibold" x-text="selectedBarbeiroNome"></span> - 
                            <span x-text="formatDate(selectedData)"></span>
                        </p>
                    </div>

                    <!-- Horários da Manhã -->
                    <div x-show="horariosManha.length > 0">
                        <h3 class="text-lg font-semibold mb-3">Manhã (09:00 - 11:30)</h3>
                        <div class="grid grid-cols-3 gap-2 mb-6">
                            <template x-for="horario in horariosManha">
                                <button @click="selectHorario(horario)"
                                        :class="selectedHorario === horario ? 'selected' : 'available'"
                                        class="py-2 px-4 rounded text-center font-medium transition-colors"
                                        x-text="horario">
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Horários da Tarde -->
                    <div x-show="horariosTarde.length > 0">
                        <h3 class="text-lg font-semibold mb-3">Tarde (14:00 - 19:00)</h3>
                        <div class="grid grid-cols-3 gap-2 mb-6">
                            <template x-for="horario in horariosTarde">
                                <button @click="selectHorario(horario)"
                                        :class="selectedHorario === horario ? 'selected' : 'available'"
                                        class="py-2 px-4 rounded text-center font-medium transition-colors"
                                        x-text="horario">
                                </button>
                            </template>
                        </div>
                    </div>

                    <div x-show="horariosManha.length === 0 && horariosTarde.length === 0" class="text-center py-8">
                        <p class="text-gray-600">Não há horários disponíveis para esta data.</p>
                        <button @click="step = 2" class="mt-4 text-blue-600 hover:text-blue-800">Escolher outra data</button>
                    </div>

                    <button @click="nextStep()" 
                            :disabled="!selectedHorario"
                            :class="selectedHorario ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-300 cursor-not-allowed'"
                            class="w-full text-white py-3 px-4 rounded-lg font-semibold transition-colors">
                        Continuar
                    </button>
                </div>

                <!-- Etapa 4: Dados do Cliente -->
                <div x-show="step === 4" class="space-y-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-2xl font-semibold text-gray-900">Seus dados</h2>
                        <button @click="step = 3" class="text-blue-600 hover:text-blue-800">← Voltar</button>
                    </div>
                    
                    <div class="bg-blue-50 p-4 rounded-lg mb-6">
                        <h3 class="font-semibold mb-2">Resumo do agendamento:</h3>
                        <p class="text-sm text-gray-700">
                            <strong>Barbeiro:</strong> <span x-text="selectedBarbeiroNome"></span><br>
                            <strong>Data:</strong> <span x-text="formatDate(selectedData)"></span><br>
                            <strong>Horário:</strong> <span x-text="selectedHorario"></span>
                        </p>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nome completo *</label>
                            <input type="text" 
                                   x-model="clienteNome"
                                   class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Digite seu nome completo">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Telefone (WhatsApp) *</label>
                            <input type="tel" 
                                   x-model="clienteTelefone"
                                   class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="(11) 99999-9999">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Observações (opcional)</label>
                            <textarea x-model="observacoes"
                                      rows="3"
                                      class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Alguma observação especial?"></textarea>
                        </div>
                    </div>

                    <button @click="confirmarAgendamento()" 
                            :disabled="!clienteNome || !clienteTelefone || loading"
                            :class="(clienteNome && clienteTelefone && !loading) ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-300 cursor-not-allowed'"
                            class="w-full text-white py-3 px-4 rounded-lg font-semibold transition-colors">
                        <span x-show="!loading">✓ Confirmar Agendamento</span>
                        <span x-show="loading">Processando...</span>
                    </button>
                </div>

                <!-- Etapa 5: Confirmação -->
                <div x-show="step === 5" class="text-center space-y-6">
                    <div class="text-green-600 text-6xl mb-4">✓</div>
                    <h2 class="text-2xl font-semibold text-gray-900">Agendamento Confirmado!</h2>
                    <div class="bg-green-50 p-6 rounded-lg">
                        <h3 class="font-semibold mb-4">Detalhes do seu agendamento:</h3>
                        <div class="text-left space-y-2">
                            <p><strong>Barbeiro:</strong> <span x-text="selectedBarbeiroNome"></span></p>
                            <p><strong>Data:</strong> <span x-text="formatDate(selectedData)"></span></p>
                            <p><strong>Horário:</strong> <span x-text="selectedHorario"></span></p>
                            <p><strong>Cliente:</strong> <span x-text="clienteNome"></span></p>
                            <p><strong>Telefone:</strong> <span x-text="clienteTelefone"></span></p>
                            <p x-show="observacoes"><strong>Observações:</strong> <span x-text="observacoes"></span></p>
                        </div>
                    </div>
                    <p class="text-gray-600">Você receberá uma confirmação em breve!</p>
                    <button @click="resetForm()" 
                            class="bg-blue-600 hover:bg-blue-700 text-white py-3 px-6 rounded-lg font-semibold transition-colors">
                        Fazer Novo Agendamento
                    </button>
                </div>
            </div>
        </div>

        <!-- Alert Messages -->
        <div x-show="alertMessage" 
             x-transition
             :class="alertType === 'error' ? 'bg-red-100 border-red-400 text-red-700' : 'bg-green-100 border-green-400 text-green-700'"
             class="fixed top-4 right-4 max-w-sm p-4 border rounded-lg shadow-lg z-50">
            <p x-text="alertMessage"></p>
            <button @click="alertMessage = ''" class="float-right text-xl leading-none">&times;</button>
        </div>
    </div>

    <script>
        function agendamentoApp() {
            return {
                step: 1,
                selectedBarbeiro: null,
                selectedBarbeiroNome: '',
                selectedData: '',
                selectedHorario: '',
                horariosManha: [],
                horariosTarde: [],
                clienteNome: '',
                clienteTelefone: '',
                observacoes: '',
                loading: false,
                alertMessage: '',
                alertType: 'success',

                selectBarbeiro(id, nome) {
                    this.selectedBarbeiro = id;
                    this.selectedBarbeiroNome = nome;
                },

                selectHorario(horario) {
                    this.selectedHorario = horario;
                },

                nextStep() {
                    this.step++;
                },

                async loadHorarios() {
                    this.loading = true;
                    try {
                        const response = await fetch(`/horarios-disponiveis?barbeiro_id=${this.selectedBarbeiro}&data=${this.selectedData}`, {
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });
                        
                        if (!response.ok) {
                            throw new Error('Erro ao carregar horários');
                        }
                        
                        const data = await response.json();
                        this.horariosManha = data.horarios_manha || [];
                        this.horariosTarde = data.horarios_tarde || [];
                        this.step = 3;
                    } catch (error) {
                        this.showAlert(error.message, 'error');
                    } finally {
                        this.loading = false;
                    }
                },

                async confirmarAgendamento() {
                    this.loading = true;
                    try {
                        const response = await fetch('/agendar', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                barbeiro_id: this.selectedBarbeiro,
                                cliente_nome: this.clienteNome,
                                cliente_telefone: this.clienteTelefone,
                                data_agendamento: this.selectedData,
                                hora_agendamento: this.selectedHorario,
                                observacoes: this.observacoes
                            })
                        });

                        const data = await response.json();
                        
                        if (response.ok) {
                            this.step = 5;
                            this.showAlert('Agendamento confirmado com sucesso!', 'success');
                        } else {
                            throw new Error(data.error || 'Erro ao confirmar agendamento');
                        }
                    } catch (error) {
                        this.showAlert(error.message, 'error');
                    } finally {
                        this.loading = false;
                    }
                },

                resetForm() {
                    this.step = 1;
                    this.selectedBarbeiro = null;
                    this.selectedBarbeiroNome = '';
                    this.selectedData = '';
                    this.selectedHorario = '';
                    this.horariosManha = [];
                    this.horariosTarde = [];
                    this.clienteNome = '';
                    this.clienteTelefone = '';
                    this.observacoes = '';
                },

                formatDate(dateStr) {
                    if (!dateStr) return '';
                    const date = new Date(dateStr + 'T00:00:00');
                    return date.toLocaleDateString('pt-BR', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });
                },

                showAlert(message, type = 'success') {
                    this.alertMessage = message;
                    this.alertType = type;
                    setTimeout(() => {
                        this.alertMessage = '';
                    }, 5000);
                }
            }
        }
    </script>
</body>
</html>
