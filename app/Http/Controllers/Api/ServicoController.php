<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServicoRequest;
use App\Http\Resources\ServicoResource;
use App\Services\ServicoService;

class ServicoController extends Controller
{
    public function __construct(
        private readonly ServicoService $service,
    ) {}

    public function porOficina(string $oficinaId)
    {
        return ServicoResource::collection($this->service->listarPorOficina($oficinaId));
    }

    public function store(ServicoRequest $request)
    {
        return (new ServicoResource($this->service->salvar($request->validated())))
            ->response()
            ->setStatusCode(201);
    }
}
