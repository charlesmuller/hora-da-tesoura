<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Barbeiro extends Model
{
    protected $fillable = [
        'nome',
        'descricao',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function agendamentos(): HasMany
    {
        return $this->hasMany(Agendamento::class);
    }

    public function agendamentosAtivos(): HasMany
    {
        return $this->hasMany(Agendamento::class)->where('status', 'agendado');
    }

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }
}
