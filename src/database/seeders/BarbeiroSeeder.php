<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BarbeiroSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Barbeiro::create([
            'nome' => 'João Silva',
            'descricao' => 'Especialista em cortes clássicos e modernos. 15 anos de experiência.',
            'ativo' => true,
        ]);

        \App\Models\Barbeiro::create([
            'nome' => 'Carlos Santos',
            'descricao' => 'Expert em barba e bigode. Atendimento personalizado.',
            'ativo' => true,
        ]);
    }
}
