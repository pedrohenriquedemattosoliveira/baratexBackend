<?php

namespace App\Services;

use App\Enums\TipoUsuario;
use App\Exceptions\NegocioException;
use App\Models\Motorista;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(
        private readonly JwtService $jwtService,
    ) {}

    public function login(string $email, string $senha): array
    {
        $usuario = Usuario::query()->whereRaw('LOWER(email) = LOWER(?)', [$email])->first();

        if (! $usuario || ! Hash::check($senha, $usuario->senha_hash)) {
            throw new NegocioException('Credenciais inválidas.');
        }

        $token = $this->jwtService->gerarToken($usuario);

        return [
            'token' => $token,
            'tipo' => 'Bearer',
            'usuarioId' => $usuario->id,
            'nome' => $usuario->nome,
            'email' => $usuario->email,
            'tipoUsuario' => $usuario->tipo_usuario->value,
        ];
    }

    /**
     * @return Usuario|string retorna a Usuario criado, ou uma string com a
     *                        mensagem de erro de negócio (400) a ser exibida.
     */
    public function registrar(array $dados): Usuario|string
    {
        $existente = Usuario::where('email', $dados['email'])->first();

        if ($existente) {
            return 'E-mail já cadastrado.';
        }

        return DB::transaction(function () use ($dados) {
            $tipoUsuario = TipoUsuario::from($dados['tipoUsuario']);

            $usuario = Usuario::create([
                'nome' => $dados['nome'],
                'email' => $dados['email'],
                'senha_hash' => Hash::make($dados['senha']),
                'telefone' => $dados['telefone'] ?? null,
                'tipo_usuario' => $tipoUsuario,
            ]);

            if ($tipoUsuario === TipoUsuario::MOTORISTA) {
                $cpf = preg_replace('/\D/', '', (string) ($dados['cpf'] ?? ''));

                if ($cpf === '') {
                    throw new NegocioException('O CPF é obrigatório para cadastrar um motorista.');
                }

                Motorista::create([
                    'usuario_id' => $usuario->id,
                    'cpf' => $cpf,
                ]);
            }

            return $usuario;
        });
    }
}
