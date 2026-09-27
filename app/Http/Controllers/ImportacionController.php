<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportarActasRequest;
use App\Services\ImportacionService;

class ImportacionController extends Controller
{
    public function __construct(private readonly ImportacionService $importacionService)
    {
    }

    public function index()
    {
        $this->authorize('create', \App\Models\User::class); // solo administrador

        return view('importaciones.index', [
            'columnas' => $this->importacionService->columnasEsperadas(),
        ]);
    }

    public function store(ImportarActasRequest $request)
    {
        $resultado = $this->importacionService->importarNacimientos(
            $request->file('archivo'),
            $request->user()
        );

        return back()->with('resultadoImportacion', $resultado);
    }
}
