<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\VeiculoRequest;
use App\Http\Resources\VeiculoResource;
use App\Services\VeiculoService;
use Illuminate\Http\Request;

class VeiculoController extends Controller
{
    public function __construct(
        private readonly VeiculoService $service,
    ) {}

    public function store(VeiculoRequest $request)
    {
        $veiculo = $this->service->cadastrarVeiculo($request->validated(), $request->user());

        return (new VeiculoResource($veiculo))->response()->setStatusCode(201);
    }

    public function index(Request $request)
    {
        return VeiculoResource::collection($this->service->listarMeusVeiculos($request->user()));
    }
}
