<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GaragemVirtualRequest;
use App\Http\Resources\GaragemVirtualResource;
use App\Services\GaragemVirtualService;

class GaragemVirtualController extends Controller
{
    public function __construct(
        private readonly GaragemVirtualService $service,
    ) {}

    public function store(GaragemVirtualRequest $request)
    {
        return (new GaragemVirtualResource($this->service->registrar($request->validated())))
            ->response()
            ->setStatusCode(200);
    }

    public function porVeiculo(string $veiculoId)
    {
        return GaragemVirtualResource::collection($this->service->listarPorVeiculo($veiculoId));
    }
}
