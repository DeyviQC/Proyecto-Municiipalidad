@php($canales = $tramite->datos['canales_entrega'] ?? [])
@php($puedeEnviar = $tramite->estado === 'LISTA')
<section class="mt-4 pt-3 border-top" aria-labelledby="titulo-enviar-documento">
<h3 id="titulo-enviar-documento">Preferencias y envío</h3>
<p class="review-muted small">Medios elegidos por el ciudadano para recibir su acta.</p>
@if(!empty($canales['correo']))
<div class="p-3 mb-3 border rounded-3"><h4 class="h6"><i class="bi bi-envelope me-2" aria-hidden="true"></i>Correo electrónico</h4>
<p class="review-muted mb-2 text-break">{{ $tramite->email }}</p>
@if(!empty($entrega['correo_enviado_at']))
<p class="text-success small">Correo enviado el {{ \Illuminate\Support\Carbon::parse($entrega['correo_enviado_at'])->format('d/m/Y H:i') }}.</p>
@elseif($puedeEnviar)
<form method="POST" action="{{ route('entrega.correo', $tramite) }}" class="mb-3">@csrf<button class="btn btn-success w-100"><i class="bi bi-envelope me-2" aria-hidden="true"></i>Enviar PDF por correo</button></form>
@else
<button class="btn btn-outline-secondary w-100" disabled>Enviar por correo al publicar</button>
@endif
<p class="review-muted small mb-0">Se envía el PDF firmado como adjunto al correo verificado, incluido Gmail.</p></div>
@endif
@if(!empty($canales['whatsapp']) && preg_match('/^[1-9][0-9]{7,14}$/D', $canales['numero'] ?? ''))
<div class="p-3 mb-3 border rounded-3"><h4 class="h6"><i class="bi bi-whatsapp me-2" aria-hidden="true"></i>WhatsApp</h4>
<p class="review-muted mb-2">+{{ $canales['numero'] }}</p>
@if($puedeEnviar)
<a href="https://wa.me/{{ $canales['numero'] }}?text={{ rawurlencode('Hola, tu documento de la solicitud N.º '.str_pad((string) $tramite->id, 6, '0', STR_PAD_LEFT).' de Registro Civil está listo.') }}" class="btn btn-outline-success w-100 mb-2" target="_blank" rel="noopener noreferrer" referrerpolicy="no-referrer"><i class="bi bi-whatsapp me-2" aria-hidden="true"></i>Abrir WhatsApp del solicitante</a>
<a href="{{ route('entrega.archivo', [$tramite, 'firmado']).'?descargar=1' }}" class="review-secondary-link">Descargar PDF firmado para adjuntar</a>
<p class="review-muted small">Si ya tienes el PDF, usa esa copia. Adjunta el documento en la conversación y pulsa enviar. Abrir el chat no confirma la entrega.</p>
@else
<button class="btn btn-outline-secondary w-100" disabled>Abrir WhatsApp al publicar</button>
@endif
</div>
@endif
@if(empty($canales['correo']) && empty($canales['whatsapp']))<p class="review-muted">El ciudadano aún no ha solicitado entrega por correo o WhatsApp. Puede descargar el documento desde su seguimiento.</p>@endif
@unless($puedeEnviar)<p class="review-muted small">Revisa y publica el PDF para habilitar los envíos solicitados.</p>@endunless
</section>
