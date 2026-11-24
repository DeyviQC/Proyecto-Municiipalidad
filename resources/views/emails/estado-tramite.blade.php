<h1>Registro Civil municipal</h1>
<p>Tu solicitud <strong>N.º {{ $numero }}</strong> cambió a: <strong>{{ $etiqueta }}</strong>.</p>
@if($indicacion)<p>{{ $indicacion }}</p>@endif
@if($mensaje !== '')<p><strong>Mensaje de Registro Civil:</strong><br>{{ $mensaje }}</p>@endif
<p><a href="{{ route('portal.seguimiento') }}">Consultar mi solicitud</a></p>
<p>Por seguridad, este correo no incluye datos del acta ni enlaces de descarga. Para ver el documento, ingresa al seguimiento y verifica tu correo.</p>
<p>Si no realizaste este trámite, puedes ignorar este mensaje.</p>
