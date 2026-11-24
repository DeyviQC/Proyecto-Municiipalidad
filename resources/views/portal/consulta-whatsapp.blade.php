@php($enlaceWhatsApp = app(\App\Services\WhatsAppConsulta::class)->enlace($tramite))
@if($tramite->pago?->estado === 'PAGADO')
<section class="mt-4 pt-4 border-top" aria-labelledby="titulo-consulta-whatsapp">
<h3 id="titulo-consulta-whatsapp" class="h6 mb-2">¿Cómo deseas recibir tu documento?</h3>
@php($canales = $tramite->datos['canales_entrega'] ?? [])
<form method="POST" action="{{ route('portal.canales') }}" class="mb-3">@csrf
<p class="small text-muted">Puedes elegir uno o ambos medios. También podrás descargarlo aquí cuando esté listo.</p>
<div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="correo" id="entrega-correo" value="1" @checked(old('correo', $canales['correo'] ?? false))><label class="form-check-label" for="entrega-correo">Recibir por correo verificado</label></div>
<div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="whatsapp" id="entrega-whatsapp" value="1" @checked(old('whatsapp', $canales['whatsapp'] ?? false))><label class="form-check-label" for="entrega-whatsapp">Recibir por WhatsApp</label></div>
<div id="campo-numero-entrega"><label class="form-label small" for="numero-entrega">Tu WhatsApp con código de país</label><input class="form-control mb-2" id="numero-entrega" name="numero" type="tel" inputmode="numeric" pattern="[1-9][0-9]{7,14}" maxlength="15" placeholder="Ej.: 51912345678" value="{{ old('numero', $canales['numero'] ?? $tramite->datos['whatsapp'] ?? '') }}"></div><p class="small text-muted">Autorizas la entrega por los medios que selecciones.</p>
<button class="btn btn-primary w-100">Guardar preferencias</button></form>
@if($enlaceWhatsApp)
<p class="small text-muted">Si deseas recibir tu documento por WhatsApp, notifica aquí:</p>
<a href="{{ $enlaceWhatsApp }}" class="btn btn-outline-success w-100" target="_blank" rel="noopener noreferrer" referrerpolicy="no-referrer"><i class="bi bi-whatsapp me-2" aria-hidden="true"></i>Notificar por WhatsApp<span class="visually-hidden"> (abre una ventana nueva)</span></a>
<p class="small text-muted mt-2 mb-0">Pulsa enviar en WhatsApp para iniciar la conversación con atención. La entrega se realiza después de la revisión municipal.</p>
@endif
</section>
@endif
