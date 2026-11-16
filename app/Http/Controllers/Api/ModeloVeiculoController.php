<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MarcaResource;
use App\Http\Resources\ModeloVeiculoResource;
use App\Models\Marca;
use App\Models\ModeloVeiculo;
use Illuminate\Http\Request;

class ModeloVeiculoController extends Controller
{
    public function marcas(Request $request)
    {
        $q = $request->query('q', '');

        $marcas = Marca::query()
            ->whereRaw('LOWER(nome) LIKE LOWER(?)', ["%{$q}%"])
            ->orderBy('nome')
            ->get();

        return MarcaResource::collection($marcas);
    }

    public function modelos(Request $request)
    {
        $request->validate(['marcaId' => ['required', 'uuid']]);

        $q = $request->query('q', '');

        $modelos = ModeloVeiculo::query()
            ->where('marca_id', $request->query('marcaId'))
            ->whereRaw('LOWER(nome_modelo) LIKE LOWER(?)', ["%{$q}%"])
            ->orderBy('nome_modelo')
            ->get();

        return ModeloVeiculoResource::collection($modelos);
    }

    public function anos(Request $request)
    {
        $request->validate(['modeloId' => ['required', 'uuid']]);

        $modelo = ModeloVeiculo::find($request->query('modeloId'));

        if (! $modelo) {
            return response()->json(null, 404);
        }

        $anoFim = $modelo->ano_fim ?? (int) date('Y');
        $anos = [];

        for ($ano = $anoFim; $ano >= $modelo->ano_inicio; $ano--) {
            $anos[] = $ano;
        }

        return response()->json($anos);
    }
}
