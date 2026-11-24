<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuardarSolicitudRequest;
use App\Models\Solicitud;
use App\Services\AuditoriaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SolicitudController extends Controller
{
    public function index(Request $request)
    {
        $interno = !$request->user()->isCiudadano();
        $datos = $request->validate(['estado' => ['nullable', Rule::in(array_keys(Solicitud::ESTADOS))]]);
        $solicitudes = Solicitud::query()->with('usuario')
            ->when(!$interno, fn ($q) => $q->where('usuario_id', $request->user()->id))
            ->when($datos['estado'] ?? null, fn ($q, $estado) => $q->where('estado', $estado))
            ->latest('id')->paginate(15)->withQueryString();
        return view('solicitudes.index', compact('solicitudes', 'interno'));
    }

    public function create() { return view('solicitudes.form', ['solicitud' => new Solicitud]); }

    public function store(GuardarSolicitudRequest $request, AuditoriaService $auditoria)
    {
        $solicitud = DB::transaction(function () use ($request, $auditoria) {
            $datos = $request->safe()->except('version');
            $solicitud = Solicitud::create($datos + ['codigo' => (string) Str::uuid(), 'usuario_id' => $request->user()->id, 'estado' => 'PRESENTADA']);
            $solicitud->eventos()->create(['usuario_id' => $request->user()->id, 'estado' => 'PRESENTADA', 'mensaje' => 'Solicitud presentada.', 'datos' => $datos]);
            $auditoria->registrar('CREAR_SOLICITUD', 'Solicitudes', $solicitud->id, 'Solicitud presentada.');
            return $solicitud;
        });
        return redirect()->route('ciudadano.solicitudes.show', $solicitud)->with('exito', 'Solicitud presentada correctamente.');
    }

    public function show(Solicitud $solicitud)
    {
        $this->authorize('view', $solicitud);
        $solicitud->load(['usuario', 'eventos']);
        return view('solicitudes.show', compact('solicitud'));
    }

    public function update(GuardarSolicitudRequest $request, Solicitud $solicitud, AuditoriaService $auditoria)
    {
        $this->authorize('update', $solicitud);
        DB::transaction(function () use ($request, $solicitud, $auditoria) {
            $actual = Solicitud::lockForUpdate()->findOrFail($solicitud->id);
            $this->authorize('update', $actual);
            $this->verificarVersion($actual, $request->integer('version'));
            $datos = $request->safe()->except('version');
            $actual->fill($datos)->forceFill(['estado' => 'PRESENTADA', 'version' => $actual->version + 1])->save();
            $actual->eventos()->create(['usuario_id' => $request->user()->id, 'estado' => 'PRESENTADA', 'mensaje' => 'El solicitante subsanó las observaciones.', 'datos' => $datos]);
            $auditoria->registrar('SUBSANAR_SOLICITUD', 'Solicitudes', $actual->id, 'Observaciones subsanadas.');
        });
        return back()->with('exito', 'La solicitud fue enviada nuevamente para revisión.');
    }

    public function atender(Request $request, Solicitud $solicitud, AuditoriaService $auditoria)
    {
        $this->authorize('atender', $solicitud);
        $datos = $request->validate(['estado' => ['required', Rule::in(array_keys(Solicitud::ESTADOS))], 'mensaje' => ['required', 'string', 'max:2000'], 'version' => ['required', 'integer', 'min:1']]);
        DB::transaction(function () use ($request, $solicitud, $datos, $auditoria) {
            $actual = Solicitud::lockForUpdate()->findOrFail($solicitud->id);
            $this->verificarVersion($actual, (int) $datos['version']);
            if (!in_array($datos['estado'], Solicitud::TRANSICIONES[$actual->estado] ?? [], true)) {
                throw ValidationException::withMessages(['estado' => 'Ese cambio de estado no está permitido.']);
            }
            $actual->forceFill(['estado' => $datos['estado'], 'version' => $actual->version + 1])->save();
            $actual->eventos()->create(['usuario_id' => $request->user()->id, 'estado' => $datos['estado'], 'mensaje' => $datos['mensaje']]);
            $auditoria->registrar('ATENDER_SOLICITUD', 'Solicitudes', $actual->id, 'Estado: '.$datos['estado']);
        });
        return back()->with('exito', 'Estado actualizado.');
    }

    private function verificarVersion(Solicitud $solicitud, int $version): void
    {
        if ($solicitud->version !== $version) {
            throw ValidationException::withMessages(['version' => 'La solicitud cambió mientras la revisabas. Recarga la página antes de continuar.']);
        }
    }
}
