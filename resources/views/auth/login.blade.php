<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Iniciar sesión | Registro Civil</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body class="login-bg d-flex align-items-center justify-content-center min-vh-100">
    <div class="card shadow-lg login-card">
        <div class="card-body p-5">
           <div class="text-center mb-4">
           <img src="{{ asset('img/logo.jpg') }}" alt="Escudo Municipal" style="width:90px;height:90px;object-fit:contain;">
           <h5 class="fw-bold mt-2 mb-0">MUNICIPALIDAD DISTRITAL</h5>
           <p class="text-muted mb-0">Andrés Avelino Cáceres Dorregaray</p>
        <p class="text-muted small">Sistema Web de Registro Civil</p>
        </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Usuario</label>
                    <input type="text" name="username" class="form-control" value="{{ old('username') }}" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label" for="remember">Recordarme</label>
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-box-arrow-in-right me-1"></i> INICIAR SESIÓN
                </button>
            </form>
        </div>
    </div>
</body>
</html>
