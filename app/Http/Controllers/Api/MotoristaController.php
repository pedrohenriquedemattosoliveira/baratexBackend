<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MotoristaRequest;
use App\Http\Resources\MotoristaResource;
use App\Services\MotoristaService;
use Illuminate\Http\Request;

class MotoristaController extends Controller
{
    public function __construct(
        private readonly MotoristaService $service,
    ) {}

    public function store(MotoristaRequest $request)
    {
        $motorista = $this->service->salvar($request->validated());

        return (new MotoristaResource($motorista->load('usuario')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(string $id)
    {
        return new MotoristaResource($this->service->buscarPorId($id));
    }

    public function me(Request $request)
    {
        return new MotoristaResource($this->service->buscarPorUsuario($request->user()));
    }
}
