<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AvaliacaoRequest;
use App\Http\Resources\AvaliacaoResource;
use App\Services\AvaliacaoService;

class AvaliacaoController extends Controller
{
    public function __construct(
        private readonly AvaliacaoService $service,
    ) {}

    public function store(AvaliacaoRequest $request)
    {
        // A API original responde 200 (ResponseEntity.ok); sem forçar o
        // status, o Laravel marcaria 201 por Model::$wasRecentlyCreated.
        return (new AvaliacaoResource($this->service->salvar($request->validated())))
            ->response()
            ->setStatusCode(200);
    }

    public function porOficina(string $oficinaId)
    {
        return AvaliacaoResource::collection($this->service->listarPorOficina($oficinaId));
    }
}
