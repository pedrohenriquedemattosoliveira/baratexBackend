<?php

namespace App\Enums;

enum TipoUsuario: string
{
    case MOTORISTA = 'MOTORISTA';
    case OFICINA = 'OFICINA';
    case ADMIN = 'ADMIN';
}
