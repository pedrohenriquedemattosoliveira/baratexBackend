<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Violação de regra de negócio (equivalente às RuntimeException lançadas
 * pelos services na API original em Spring Boot). Tratada globalmente em
 * bootstrap/app.php e convertida para o payload de erro padrão da API.
 */
class NegocioException extends RuntimeException {}
