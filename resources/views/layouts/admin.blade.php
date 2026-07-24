<!DOCTYPE html>
<html lang="es" id="htmlElement" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <head>
    <meta charset="UTF-8">
    <!-- 🔥 SCRIPT DE BLOQUEO PARA EVITAR EL FLASH BLANCO -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme');
            const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const theme = savedTheme || (systemPrefersDark ? 'dark' : 'light');
            document.documentElement.setAttribute('data-bs-theme', theme);
        })();
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sistema') | {{ \App\Helpers\ContentHelper::getNombreMostrar() }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>

        body {
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        
        .required-asterisk {
            color: red;
            margin-left: 2px;
        }
        .required-label::after {
            content: " *";
            color: red;
            font-weight: bold;
        }

        /* Botón flotante */
        .floating-btn {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            box-shadow: 0 4px 12px rgba(0,0,0,0.25);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            z-index: 1050;
        }
        .floating-btn:hover {
            transform: scale(1.08);
            box-shadow: 0 6px 20px rgba(0,0,0,0.35);
        }

        /* Estilos Sidebar y Layout */
        #sidebar {
            width: 260px;
            transition: margin-left 0.3s ease-in-out;
            z-index: 1040;
        }

        @media (min-width: 992px) {
            #sidebar.collapsed {
                margin-left: -260px;
            }
        }

        .nav-link.active-submenu {
            background-color: rgba(13, 110, 253, 0.15);
            border-left: 3px solid #0d6efd;
            color: #0d6efd !important;
        }
    </style>
</head>

<body class="bg-body">

<div class="d-flex min-vh-100 position-relative">

    <aside id="sidebar" class="bg-dark text-white vh-100 p-3 shadow position-sticky top-0 d-flex flex-column flex-shrink-0" data-bs-theme="dark">
        
        <div class="text-center mb-3">
            @php
                $logoMostrar = \App\Helpers\ContentHelper::getLogoActual();
            @endphp
            
            @if($logoMostrar)
                <img
                    src="{{ asset('storage/' . $logoMostrar) }}"
                    class="img-fluid rounded-3"
                    style="max-height: 75px; object-fit: contain;"
                    alt="Logo"
                >
            @else
                <div class="d-inline-flex align-items-center justify-content-center bg-secondary bg-opacity-25 rounded-circle" style="width: 60px; height: 60px;">
                    <i class="bi bi-building fs-3 text-white-50"></i>
                </div>
            @endif
        </div>

        <h6 class="text-center text-uppercase fw-bold text-wrap px-2 mb-4 tracking-tight" style="color: #f8fafc; font-size: 13px; letter-spacing: 0.5px;">
            {{ \App\Helpers\ContentHelper::getNombreMostrar() }}
        </h6>

        <hr class="text-white-50 my-2">

        <ul class="nav flex-column mb-auto overflow-y-auto">
            <li class="nav-item mb-1">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'text-primary fw-bold' : 'text-white' }}">
                    <i class="bi bi-speedometer2 me-2"></i>
                    Dashboard
                </a>
            </li>

            <li class="nav-item mb-1">
                <a href="{{ route('rentas.index') }}" class="nav-link {{ request()->routeIs('rentas.*') ? 'text-primary fw-bold' : 'text-white' }}">
                    <i class="bi bi-file-earmark-text me-2"></i>
                    Rentas
                </a>
            </li>

            <li class="nav-item mb-1">
                <a href="{{ route('inventario.index') }}" class="nav-link {{ request()->routeIs('inventario.*') ? 'text-primary fw-bold' : 'text-white' }}">
                    <i class="bi bi-box-seam me-2"></i>
                    Inventario
                </a>
            </li>

            <!-- Movimientos entre sucursales -->
            <li class="nav-item mb-2">
                <a href="{{ route('movimientos.index') }}" class="nav-link {{ request()->routeIs('movimientos.*') ? 'text-primary fw-bold' : 'text-white' }}">
                    <i class="bi bi-arrow-left-right me-2"></i>
                    Movimientos
                </a>
            </li>

            <li class="nav-item mb-1">
                <a href="{{ route('obras.index') }}" class="nav-link {{ request()->routeIs('obras.*') ? 'text-primary fw-bold' : 'text-white' }}">
                    <i class="bi bi-building me-2"></i>
                    Obras
                </a>
            </li>

            <li class="nav-item mb-1">
                <a href="{{ route('puntoventa.index') }}" class="nav-link {{ request()->routeIs('puntoventa.*') ? 'text-primary fw-bold' : 'text-white' }}">
                    <i class="bi bi-cart3 me-2"></i>
                    Punto de Venta
                </a>
            </li>

            <li class="nav-item mb-1">
                <a href="{{ route('clientes.index') }}" class="nav-link {{ request()->routeIs('clientes.*') ? 'text-primary fw-bold' : 'text-white' }}">
                    <i class="bi bi-people me-2"></i>
                    Clientes
                </a>
            </li>

            <li class="nav-item mb-1">
                <a href="{{ route('configuracion.index') }}" class="nav-link {{ request()->routeIs('configuracion.*') ? 'text-primary fw-bold' : 'text-white' }}">
                    <i class="bi bi-sliders me-2"></i>
                    Configuración
                </a>
            </li>
        </ul>
    </aside>

    <main class="flex-grow-1 d-flex flex-column w-100" style="min-width: 0; overflow-x: hidden;">
        
        <nav class="navbar bg-body border-bottom shadow-sm px-2 px-md-3 py-2 sticky-top">
            <div class="container-fluid p-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-outline-secondary border-0 p-1 px-2" id="sidebarToggle" type="button" aria-label="Toggle Sidebar">
                        <i class="bi bi-list fs-4"></i>
                    </button>
                    
                    <h5 class="mb-0 fw-bold fs-6 text-truncate" style="max-width: 200px;">
                        @yield('page-title', 'Panel de Control')
                    </h5>
                </div>

                <div class="d-flex align-items-center">
                    <button class="btn btn-outline-secondary btn-sm rounded-circle p-0 d-flex align-items-center justify-content-center me-3" 
                            id="themeToggle" 
                            type="button" 
                            style="width: 34px; height: 34px;"
                            title="Cambiar tema">
                        <i class="bi bi-moon-stars-fill fs-6" id="themeIcon"></i>
                    </button>

                    @php
                        $sucursalInfo = \App\Helpers\ContentHelper::getSucursalActiva();
                    @endphp
                    
                    <div class="me-4 text-end d-none d-md-block border-end pe-3">
                        <span class="text-muted d-block" style="font-size: 11px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">
                            @if(\App\Helpers\ContentHelper::isSucursalEspecifica())
                                Sucursal Activa
                            @else
                                Consola
                            @endif
                        </span>
                        <span class="badge bg-body-secondary text-body fw-bold small rounded-pill px-3 border">
                            <i class="bi bi-geo-alt-fill text-primary me-1"></i>
                            {{ $sucursalInfo['nombre'] }}
                        </span>
                    </div>

                    <div class="text-end me-3">
                        <span class="d-block fw-semibold text-body" style="font-size: 13px;">{{ Auth::user()->name }}</span>
                        <span class="text-muted text-capitalize d-block" style="font-size: 11px;">
                            @if(Auth::user()->isAdmin())
                                Administrador Global
                            @elseif(Auth::user()->isGerente())
                                Gerente de Sucursal
                            @else
                                Cajero / POS
                            @endif
                        </span>
                    </div>

                    <form action="{{ route('logout') }}" method="POST" class="m-0">
                        @csrf
                        <button class="btn btn-outline-danger btn-sm rounded-3 fw-bold px-2 py-1" style="font-size: 12px;" title="Cerrar sesión">
                            <i class="bi bi-box-arrow-right"></i>
                            <span class="d-none d-md-inline ms-1">Salir</span>
                        </button>
                    </form>
                </div>
            </div>
        </nav>

        <div class="p-2 p-sm-3 p-md-4 flex-grow-1">
            <div class="container-fluid p-0">
                @yield('content')
            </div>
        </div>
    </main>

</div>

<!-- Botón flotante con dropdown -->
<div class="position-fixed bottom-0 end-0 p-3 p-md-4" style="z-index: 1050;">
    <div class="dropdown dropup">
        <button class="btn btn-primary floating-btn dropdown-toggle d-flex align-items-center justify-content-center" 
                type="button" 
                data-bs-toggle="dropdown" 
                aria-expanded="false"
                title="Acciones rápidas">
            <i class="bi bi-plus-lg fs-3"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 py-2 mb-2" style="min-width: 220px;">
            <li>
                <a class="dropdown-item d-flex align-items-center py-2" href="{{ route('movimientos.create') }}">
                    <span class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                        <i class="bi bi-arrow-left-right text-primary"></i>
                    </span>
                    <div>
                        <span class="d-block fw-semibold">Nuevo Movimiento</span>
                        <small class="text-muted">Transferir entre sucursales</small>
                    </div>
                </a>
            </li>
            <li>
                <a class="dropdown-item d-flex align-items-center py-2" href="{{ route('movimientos.index') }}">
                    <span class="bg-info bg-opacity-10 rounded-circle p-2 me-3">
                        <i class="bi bi-clock-history text-info"></i>
                    </span>
                    <div>
                        <span class="d-block fw-semibold">Historial</span>
                        <small class="text-muted">Ver todos los movimientos</small>
                    </div>
                </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <a class="dropdown-item d-flex align-items-center py-2" href="{{ route('inventario.create') }}">
                    <span class="bg-success bg-opacity-10 rounded-circle p-2 me-3">
                        <i class="bi bi-box-seam text-success"></i>
                    </span>
                    <div>
                        <span class="d-block fw-semibold">Nuevo Producto</span>
                        <small class="text-muted">Agregar al inventario</small>
                    </div>
                </a>
            </li>
            <li>
                <a class="dropdown-item d-flex align-items-center py-2" href="{{ route('rentas.create') }}">
                    <span class="bg-warning bg-opacity-10 rounded-circle p-2 me-3">
                        <i class="bi bi-file-earmark-text text-warning"></i>
                    </span>
                    <div>
                        <span class="d-block fw-semibold">Nueva Renta</span>
                        <small class="text-muted">Registrar renta de equipo</small>
                    </div>
                </a>
            </li>
        </ul>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 1. Toggle Sidebar
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');

        if (sidebarToggle && sidebar) {
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
            });
        }

        // 2. Control de Tema Oscuro / Claro
        const htmlElement = document.getElementById('htmlElement');
        const themeToggleBtn = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');

        if (themeToggleBtn && htmlElement && themeIcon) {
            const getPreferredTheme = () => {
                const storedTheme = localStorage.getItem('theme');
                if (storedTheme) {
                    return storedTheme;
                }
                return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            };

            const setTheme = (theme) => {
                htmlElement.setAttribute('data-bs-theme', theme);
                localStorage.setItem('theme', theme);

                if (theme === 'dark') {
                    themeIcon.className = 'bi bi-sun-fill';
                    themeToggleBtn.classList.remove('btn-outline-secondary');
                    themeToggleBtn.classList.add('btn-outline-warning', 'text-warning');
                } else {
                    themeIcon.className = 'bi bi-moon-stars-fill';
                    themeToggleBtn.classList.remove('btn-outline-warning', 'text-warning');
                    themeToggleBtn.classList.add('btn-outline-secondary');
                }
            };

            // Cargar tema inicial
            setTheme(getPreferredTheme());

            // Evento Click
            themeToggleBtn.addEventListener('click', () => {
                const currentTheme = htmlElement.getAttribute('data-bs-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                setTheme(newTheme);
            });
        }

        // 3. Inicializar tooltips de Bootstrap si existen
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>

</body>
</html>