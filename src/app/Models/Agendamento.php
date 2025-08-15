<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Agendamento extends Model
{
    protected $fillable = [
        'barbeiro_id',
        'cliente_nome',
        'cliente_telefone',
        'data_agendamento',
        'hora_agendamento',
        'status',
        'observacoes',
    ];

    protected $casts = [
        'data_agendamento' => 'date',
        'hora_agendamento' => 'datetime:H:i',
    ];

    public function barbeiro(): BelongsTo
    {
        return $this->belongsTo(Barbeiro::class);
    }

    public function getDataHoraAttribute()
    {
        return Carbon::parse($this->data_agendamento->format('Y-m-d') . ' ' . $this->hora_agendamento->format('H:i'));
    }

    public function scopeAgendados($query)
    {
        return $query->where('status', 'agendado');
    }

    public function scopeHoje($query)
    {
        return $query->whereDate('data_agendamento', today());
    }

    public function scopeFuturo($query)
    {
        return $query->whereDate('data_agendamento', '>=', today());
    }
}
