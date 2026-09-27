<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titulo', 'Registro Civil') | Municipalidad</title>
<link rel="icon" type="image/jpeg" href="{{ asset('img/logo.jpg') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @stack('estilos')
</head>
<body>
    <div class="d-flex" id="app-wrapper">
        <!-- Sidebar -->
        <nav class="sidebar-municipal d-flex flex-column p-3" id="sidebar">
            <div class="text-center mb-4">
                <img src="{{ asset('img/logo.jpg') }}" alt="Escudo Municipal" style="width:70px;height:70px;object-fit:contain;">
                <h6 class="text-white mt-2 mb-0">MUNICIPALIDAD DISTRITAL</h6>
                <small class="text-white-50">Andrés Avelino Cáceres Dorregaray</small>
            </div>
            <ul class="nav nav-pills flex-column gap-1">
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('actas.create') }}" class="nav-link {{ request()->routeIs('actas.create') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-plus me-2"></i> Registrar acta
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('actas.index') }}" class="nav-link {{ request()->routeIs('actas.index') || request()->routeIs('actas.show') ? 'active' : '' }}">
                        <i class="bi bi-search me-2"></i> Buscar actas
                    </a>
                </li>
                @can('viewAny', App\Models\User::class)
                <li class="nav-item">
                    <a href="{{ route('usuarios.index') }}" class="nav-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}">
                        <i class="bi bi-people me-2"></i> Usuarios
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('auditoria.index') }}" class="nav-link {{ request()->routeIs('auditoria.*') ? 'active' : '' }}">
                        <i class="bi bi-clipboard-data me-2"></i> Auditoría
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('importaciones.index') }}" class="nav-link {{ request()->routeIs('importaciones.*') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-arrow-up me-2"></i> Importación masiva
                    </a>
                </li>
                @endcan
            </ul>
            <div class="mt-auto pt-3 border-top border-light-subtle">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-link text-white-50 text-decoration-none p-0">
                        <i class="bi bi-box-arrow-right me-2"></i> Cerrar sesión
                    </button>
                </form>
            </div>
        </nav>

        <!-- Contenido -->
        <div class="flex-grow-1">
            <header class="header-municipal d-flex align-items-center justify-content-between px-4 py-3">
                <div>
                    <h5 class="mb-0">@yield('titulo', 'Sistema Web de Registro Civil')</h5>
                </div>
                <div class="text-end">
                    <div class="fw-semibold">{{ auth()->user()->name }}</div>
                    <small class="text-muted">
                        {{ auth()->user()->isAdmin() ? 'Administrador' : 'Asistente' }}
                    </small>
                </div>
            </header>

            <main class="p-4">
                @if (session('exito'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-1"></i> {{ session('exito') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <strong>Se encontraron errores:</strong>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('contenido')
            </main>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
