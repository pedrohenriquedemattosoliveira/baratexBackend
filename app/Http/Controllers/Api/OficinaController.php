<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OficinaRequest;
use App\Http\Resources\OficinaResource;
use App\Services\OficinaService;
use Illuminate\Http\Request;

class OficinaController extends Controller
{
    public function __construct(
        private readonly OficinaService $service,
    ) {}

    public function index()
    {
        return OficinaResource::collection($this->service->listarTodas());
    }

    public function store(OficinaRequest $request)
    {
        $oficina = $this->service->salvar($request->validated());

        return (new OficinaResource($oficina))->response()->setStatusCode(201);
    }

    public function show(string $id)
    {
        return new OficinaResource($this->service->buscarPorId($id));
    }

    public function me(Request $request)
    {
        return new OficinaResource($this->service->buscarPorUsuario($request->user()));
    }

    public function proximas(Request $request)
    {
        $validado = $request->validate([
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'raioKm' => ['nullable', 'numeric'],
        ]);

        $oficinas = $this->service->buscarProximos(
            (float) $validado['latitude'],
            (float) $validado['longitude'],
            (float) ($validado['raioKm'] ?? 10),
        );

        return OficinaResource::collection($oficinas);
    }
}
