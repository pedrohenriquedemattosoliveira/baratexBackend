<?php

namespace App\Enums;

enum StatusAgendamento: string
{
    case PENDENTE = 'PENDENTE';
    case CONFIRMADO = 'CONFIRMADO';
    case EM_ANDAMENTO = 'EM_ANDAMENTO';
    case CONCLUIDO = 'CONCLUIDO';
    case CANCELADO = 'CANCELADO';
}
