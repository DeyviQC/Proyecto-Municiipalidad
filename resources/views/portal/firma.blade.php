@php($entrega = $tramite->datos['entrega'] ?? [])
@if($tramite->estado === 'LISTA')
<p class="review-muted">El documento ya está disponible en el seguimiento del ciudadano.</p>
<a class="btn btn-success w-100" href="{{ route('entrega.archivo', [$tramite, 'firmado']) }}" target="_blank" rel="noopener">Ver documento publicado</a>
@include('portal.canales-entrega')
@elseif(empty($entrega['firmado_ruta']))
<div class="manual-sign">
<div class="signature-launch">
<a class="btn btn-success w-100" href="rcmunicipal-firma://abrir">Abrir Firma Perú</a>
<p class="review-muted">Firma tu documento y vuelve aquí para subirlo.</p>
<a class="signature-download" href="{{ route('entrega.archivo', [$tramite, 'original']).'?descargar=1' }}"><i class="bi bi-download me-2" aria-hidden="true"></i>Descargar para firmar <span>(opcional)</span></a>
</div>
<section class="signature-upload">
<form method="POST" enctype="multipart/form-data" action="{{ route('entrega.cargar', $tramite) }}">@csrf<input type="hidden" name="version" value="{{ $tramite->version }}">
<label class="form-label" for="archivo_firmado">PDF firmado</label>
<input id="archivo_firmado" name="archivo" type="file" accept="application/pdf" class="form-control" aria-describedby="archivo_firmado_ayuda" required>
<p id="archivo_firmado_ayuda" class="review-muted">Máximo 20 MB.</p>
<button class="btn btn-success w-100">Comprobar y guardar</button>
</form>
</section>
</div>
@else
<h3>Revisa y publica el documento</h3><p class="review-muted">Firmante: <strong>{{ $entrega['firmante'] }}</strong></p>
@include('portal.canales-entrega')
<a class="btn btn-outline-success w-100 mb-3" href="{{ route('entrega.archivo', [$tramite, 'firmado']) }}" target="_blank" rel="noopener">Revisar PDF firmado ↗</a>
<p class="review-muted">La integridad del archivo se comprobó. Revisa también el certificado y la autorización del firmante antes de entregarlo.</p>
<form method="POST" action="{{ route('entrega.publicar', $tramite) }}" data-confirm="¿Publicar el PDF revisado? El ciudadano podrá descargarlo.">@csrf<input type="hidden" name="version" value="{{ $tramite->version }}"><div class="form-check mb-3"><input class="form-check-input" id="revision_firma" name="revision_firma" type="checkbox" value="1" required><label class="form-check-label" for="revision_firma">Verifiqué el contenido, la validez y revocación del certificado, y la autorización institucional del firmante.</label></div><label class="form-label" for="sustento_firma">Constancia de revisión</label><textarea id="sustento_firma" name="sustento_firma" class="form-control mb-3" rows="3" minlength="15" maxlength="1500" required>{{ old('sustento_firma') }}</textarea><button class="btn btn-success w-100">Publicar documento</button></form>
<details class="review-details"><summary>Retirar un archivo incorrecto</summary><form class="mt-3" method="POST" action="{{ route('entrega.descartar', $tramite) }}" data-confirm="¿Retirar este borrador firmado? Tendrá que cargar el archivo correcto.">@csrf<input type="hidden" name="version" value="{{ $tramite->version }}"><label for="motivo_descarte" class="form-label">Motivo</label><textarea id="motivo_descarte" name="motivo_descarte" minlength="10" maxlength="500" class="form-control mb-3" required></textarea><button class="btn btn-outline-danger w-100">Retirar borrador</button></form></details>
@endif
