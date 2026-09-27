<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreActaRequest;
use App\Http\Requests\StoreDefuncionRequest;
use App\Http\Requests\StoreMatrimonioRequest;
use App\Http\Requests\StoreNacimientoRequest;
use App\Models\Acta;
use App\Services\ActaService;
use App\Services\AuditoriaService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ActaController extends Controller
{
    public function __construct(
        private readonly ActaService $actaService,
        private readonly AuditoriaService $auditoriaService,
    ) {
    }

    /**
     * Búsqueda y listado de actas (Sección 20).
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Acta::class);

        $filtros = $request->only([
            'numero_acta', 'anio', 'tipo', 'dni', 'nombre', 'apellido',
            'fecha_desde', 'fecha_hasta',
        ]);

        $actas = $this->actaService->buscar($filtros);

        return view('actas.index', compact('actas', 'filtros'));
    }

    public function create()
    {
        $this->authorize('create', Acta::class);

        return view('actas.create');
    }

    /**
     * Registra el acta completa: valida el request "envoltorio" (StoreActaRequest)
     * y además el sub-formulario específico según el tipo, en la misma petición.
     */
    public function store(StoreActaRequest $request)
    {
        $this->authorize('create', Acta::class);

        $datosActa = $request->validated();
        $tipo = $datosActa['tipo'];

        // Validamos el sub-formulario correspondiente al tipo de acta,
        // reutilizando las reglas de cada FormRequest sin necesitar rutas separadas.
        $reglasDetalle = match ($tipo) {
            Acta::TIPO_NACIMIENTO => (new StoreNacimientoRequest())->rules(),
            Acta::TIPO_MATRIMONIO => (new StoreMatrimonioRequest())->rules(),
            Acta::TIPO_DEFUNCION => (new StoreDefuncionRequest())->rules(),
        };

        $datosDetalle = $request->validate($reglasDetalle);

        try {
            $acta = $this->actaService->registrar(
                $datosActa,
                $datosDetalle,
                $request->file('pdf'),
                $request->user()
            );
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()
            ->route('actas.show', $acta)
            ->with('exito', 'Acta registrada correctamente. El acta ha sido bloqueada y queda disponible únicamente para consulta.');
    }

    public function show(Acta $acta)
    {
        $this->authorize('view', $acta);

        $acta->load(['nacimiento', 'matrimonio', 'defuncion', 'usuario']);

        $this->auditoriaService->registrar(
            AuditoriaService::CONSULTAR_ACTA,
            'Registro Civil',
            $acta->id,
            "Consulta del acta {$acta->tipo} N° {$acta->numero_acta}/{$acta->anio}."
        );

        return view('actas.show', compact('acta'));
    }

    /**
     * Vista optimizada para impresión (Sección 23). Sin sidebar/menú.
     */
    public function imprimir(Acta $acta)
    {
        $this->authorize('view', $acta);

        $acta->load(['nacimiento', 'matrimonio', 'defuncion', 'usuario']);

        $this->auditoriaService->registrar(
            AuditoriaService::IMPRIMIR_PDF,
            'Registro Civil',
            $acta->id,
            "Impresión del acta {$acta->tipo} N° {$acta->numero_acta}/{$acta->anio}."
        );

        return view('actas.print', compact('acta'));
    }

    /**
     * Bloqueo explícito: nunca se expone update/destroy en las rutas
     * (ver routes/web.php), pero además la Policy lo niega siempre.
     * Estos métodos existen solo para devolver un 403 claro si alguien
     * intenta forzar la URL.
     */
    public function edit(Acta $acta)
    {
        $this->authorize('update', $acta);
    }

    public function destroy(Acta $acta)
    {
        $this->authorize('delete', $acta);
    }
}
