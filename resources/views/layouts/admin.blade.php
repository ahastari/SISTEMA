<!DOCTYPE html>
<html lang="es" id="htmlElement" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sistema') | {{ \App\Helpers\ContentHelper::getNombreMostrar() }}</title>

    <!-- 🔥 SCRIPT DE BLOQUEO INSTANTÁNEO (EVITA FLASH BLANCO Y PARPADEO DEL MENU) -->
    <script>
        (function() {
            // 1. Cargar tema de forma inmediata
            const savedTheme = localStorage.getItem('theme');
            const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const theme = savedTheme || (systemPrefersDark ? 'dark' : 'light');
            document.documentElement.setAttribute('data-bs-theme', theme);

            // 2. Cargar estado del Sidebar
            const isSmallScreen = window.innerWidth < 1200;
            const savedSidebar = localStorage.getItem('sidebar-collapsed');

            if (isSmallScreen || savedSidebar === 'true') {
                document.documentElement.classList.add('init-sidebar-collapsed');
            }
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        
        .required-asterisk { color: red; margin-left: 2px; }
        .required-label::after { content: " *"; color: red; font-weight: bold; }

        .floating-btn {
            width: 56px; height: 56px; border-radius: 50%;
            box-shadow: 0 4px 12px rgba(0,0,0,0.25);
            transition: transform 0.2s ease, box-shadow 0.2s ease; z-index: 1050;
        }
        .floating-btn:hover { transform: scale(1.08); box-shadow: 0 6px 20px rgba(0,0,0,0.35); }

        /* ESTILOS BASE SIDEBAR */
        #sidebar {
            width: 260px;
            transition: width 0.25s cubic-bezier(0.4, 0, 0.2, 1), padding 0.25s ease;
            z-index: 1040;
            overflow-x: hidden;
        }

        /* CONTROL DE TAMAÑO DE LOGO DESPLEGADO */
        .sidebar-logo-container img {
            max-height: 100px !important;
            width: 85% !important;
            max-width: 200px !important;
            object-fit: contain;
            transition: all 0.25s ease;
        }

        /* REGLA GENERAL COLAPSADO (MINI-SIDEBAR CON SOLO ICONOS) */
        html.init-sidebar-collapsed #sidebar,
        #sidebar.collapsed {
            width: 70px !important;
            padding-left: 0.5rem !important;
            padding-right: 0.5rem !important;
        }

        html.init-sidebar-collapsed #sidebar .sidebar-text,
        html.init-sidebar-collapsed #sidebar .sidebar-brand-title,
        html.init-sidebar-collapsed #sidebar hr,
        #sidebar.collapsed .sidebar-text,
        #sidebar.collapsed .sidebar-brand-title,
        #sidebar.collapsed hr {
            display: none !important;
        }

        #sidebar.collapsed .sidebar-logo-container img,
        html.init-sidebar-collapsed #sidebar .sidebar-logo-container img {
            max-height: 40px !important;
            width: 100% !important;
        }

        #sidebar.collapsed .nav-link,
        html.init-sidebar-collapsed #sidebar .nav-link {
            text-align: center;
            padding: 10px 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        #sidebar.collapsed .nav-link i,
        html.init-sidebar-collapsed #sidebar .nav-link i {
            margin-right: 0 !important;
            font-size: 1.3rem;
        }

        /* RESPONSIVE AUTOMÁTICO PARA PANTALLAS PEQUEÑAS (< 1200px) */
        @media (max-width: 1199.98px) {
            #sidebar:not(.user-expanded) {
                width: 70px !important;
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
            }

            #sidebar:not(.user-expanded) .sidebar-text,
            #sidebar:not(.user-expanded) .sidebar-brand-title,
            #sidebar:not(.user-expanded) hr {
                display: none !important;
            }

            #sidebar:not(.user-expanded) .sidebar-logo-container img {
                max-height: 40px !important;
                width: 100% !important;
            }

            #sidebar:not(.user-expanded) .nav-link {
                text-align: center;
                padding: 10px 0;
                display: flex;
                justify-content: center;
                align-items: center;
            }

            #sidebar:not(.user-expanded) .nav-link i {
                margin-right: 0 !important;
                font-size: 1.3rem;
            }
        }

        #sidebar.collapsed .badge-autorizaciones,
        html.init-sidebar-collapsed #sidebar .badge-autorizaciones {
            position: absolute !important;
            top: 6px;
            right: 12px;
            font-size: 9px !important;
            padding: 3px 5px !important;
        }

        @media (max-width: 1199.98px) {
            #sidebar:not(.user-expanded) .badge-autorizaciones {
                position: absolute !important;
                top: 6px;
                right: 12px;
                font-size: 9px !important;
                padding: 3px 5px !important;
            }
        }
    </style>
</head>

<body class="bg-body">

<div class="d-flex min-vh-100 position-relative">

    <!-- SIDEBAR -->
    <aside id="sidebar" class="bg-dark text-white vh-100 p-3 shadow position-sticky top-0 d-flex flex-column flex-shrink-0" data-bs-theme="dark">
        
        <!-- LOGO DE SUCURSAL / MATRIZ -->
        <div class="text-center mb-2 sidebar-logo-container">
            @php
                $logoUrl = \App\Helpers\ContentHelper::getLogoActual();
            @endphp
            
            @if(!empty($logoUrl))
                <img
                    src="{{ $logoUrl }}"
                    class="img-fluid rounded-3 mx-auto d-block"
                    alt="Logo Empresa"
                    style="max-height: 90px; object-fit: contain;"
                    onerror="console.error('No se pudo cargar el logo desde URL:', this.src); this.style.display='none'; document.getElementById('placeholderLogo').classList.remove('d-none'); document.getElementById('placeholderLogo').classList.add('d-inline-flex');"
                >
                <div id="placeholderLogo" class="d-none align-items-center justify-content-center bg-secondary bg-opacity-25 rounded-circle mx-auto" style="width: 48px; height: 48px;">
                    <i class="bi bi-building fs-4 text-white-50"></i>
                </div>
            @else
                <div class="d-inline-flex align-items-center justify-content-center bg-secondary bg-opacity-25 rounded-circle mx-auto" style="width: 48px; height: 48px;">
                    <i class="bi bi-building fs-4 text-white-50"></i>
                </div>
            @endif
        </div>

        <h6 class="text-center text-uppercase fw-bold text-wrap px-2 mb-3 tracking-tight sidebar-brand-title" style="color: #f8fafc; font-size: 12px; letter-spacing: 0.5px;">
            {{ \App\Helpers\ContentHelper::getNombreMostrar() }}
        </h6>

        <hr class="text-white-50 my-2">

        <!-- MENÚ PRINCIPAL -->
        <ul class="nav nav-pills flex-column mb-auto overflow-y-auto">
            
            @if(Auth::user()->isAdmin() || Auth::user()->isGerente())
            <li class="nav-item mb-1">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : 'text-white' }}" title="Dashboard">
                    <i class="bi bi-speedometer2 me-2"></i>
                    <span class="sidebar-text">Dashboard</span>
                </a>
            </li>
            @endif

            <li class="nav-item mb-1">
                <a href="{{ route('rentas.index') }}" class="nav-link {{ request()->routeIs('rentas.*') ? 'active' : 'text-white' }}" title="Rentas">
                    <i class="bi bi-file-earmark-text me-2"></i>
                    <span class="sidebar-text">Rentas</span>
                </a>
            </li>

            @if(Auth::user()->isAdmin() || Auth::user()->isGerente())
            <li class="nav-item mb-1">
                <a href="{{ route('autorizaciones.index') }}" class="nav-link position-relative d-flex align-items-center {{ request()->routeIs('autorizaciones.*') ? 'text-warning fw-bold' : 'text-white' }}" title="Autorizaciones">
                    <i class="bi bi-shield-lock me-2"></i>
                    <span class="sidebar-text me-auto">Autorizaciones</span>
                    <span id="badge-autorizaciones" class="badge bg-danger rounded-pill shadow-sm badge-autorizaciones" style="display: none; font-size: 11px;">0</span>
                </a>
            </li>
            @endif

            <li class="nav-item mb-1">
                <a href="{{ route('inventario.index') }}" class="nav-link {{ request()->routeIs('inventario.*') ? 'active' : 'text-white' }}" title="Inventario">
                    <i class="bi bi-box-seam me-2"></i>
                    <span class="sidebar-text">Inventario</span>
                </a>
            </li>

            <li class="nav-item mb-1">
                <a href="{{ route('movimientos.index') }}" class="nav-link {{ request()->routeIs('movimientos.*') ? 'active' : 'text-white' }}" title="Movimientos">
                    <i class="bi bi-arrow-left-right me-2"></i>
                    <span class="sidebar-text">Movimientos</span>
                </a>
            </li>

            <li class="nav-item mb-1">
                <a href="{{ route('obras.index') }}" class="nav-link {{ request()->routeIs('obras.*') ? 'active' : 'text-white' }}" title="Obras">
                    <i class="bi bi-building me-2"></i>
                    <span class="sidebar-text">Obras</span>
                </a>
            </li>

            <li class="nav-item mb-1">
                <a href="{{ route('puntoventa.index') }}" class="nav-link {{ request()->routeIs('puntoventa.*') ? 'active' : 'text-white' }}" title="Punto de Venta">
                    <i class="bi bi-cart3 me-2"></i>
                    <span class="sidebar-text">Punto de Venta</span>
                </a>
            </li>

            <li class="nav-item mb-1">
                <a href="{{ route('clientes.index') }}" class="nav-link {{ request()->routeIs('clientes.*') ? 'active' : 'text-white' }}" title="Clientes">
                    <i class="bi bi-people me-2"></i>
                    <span class="sidebar-text">Clientes</span>
                </a>
            </li>

            @if(Auth::user()->isAdmin() || Auth::user()->isGerente())
            <li class="nav-item mb-1">
                <a href="{{ route('configuracion.index') }}" class="nav-link {{ request()->routeIs('configuracion.*') ? 'active' : 'text-white' }}" title="Configuración">
                    <i class="bi bi-sliders me-2"></i>
                    <span class="sidebar-text">Configuración</span>
                </a>
            </li>
            @endif
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
                    <!-- BOTÓN CAMBIO DE TEMA OSCURO / CLARO -->
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const htmlDoc = document.documentElement;

        // 1. LÓGICA RESPONSIVE Y ESTADO DEL SIDEBAR
        const checkResponsiveSidebar = () => {
            const isSmallScreen = window.innerWidth < 1200;
            const savedState = localStorage.getItem('sidebar-collapsed');

            if (isSmallScreen) {
                sidebar.classList.add('collapsed');
                htmlDoc.classList.add('init-sidebar-collapsed');
            } else {
                if (savedState === 'true') {
                    sidebar.classList.add('collapsed');
                    htmlDoc.classList.add('init-sidebar-collapsed');
                } else {
                    sidebar.classList.remove('collapsed');
                    htmlDoc.classList.remove('init-sidebar-collapsed');
                }
            }
        };

        checkResponsiveSidebar();
        window.addEventListener('resize', checkResponsiveSidebar);

        if (sidebarToggle && sidebar) {
            sidebarToggle.addEventListener('click', function() {
                const isCollapsed = sidebar.classList.toggle('collapsed');
                
                if (isCollapsed) {
                    htmlDoc.classList.add('init-sidebar-collapsed');
                    sidebar.classList.remove('user-expanded');
                } else {
                    htmlDoc.classList.remove('init-sidebar-collapsed');
                    sidebar.classList.add('user-expanded');
                }
                
                localStorage.setItem('sidebar-collapsed', isCollapsed);
            });
        }

        // 2. CONMUTADOR DE TEMA OSCURO / CLARO
        const themeToggleBtn = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');

        if (themeToggleBtn && themeIcon) {
            const getPreferredTheme = () => {
                const storedTheme = localStorage.getItem('theme');
                if (storedTheme) {
                    return storedTheme;
                }
                return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            };

            const applyTheme = (theme) => {
                htmlDoc.setAttribute('data-bs-theme', theme);
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

            // Aplicar tema guardado al cargar la página
            applyTheme(getPreferredTheme());

            // Evento click para alternar entre oscuro y claro
            themeToggleBtn.addEventListener('click', () => {
                const currentTheme = htmlDoc.getAttribute('data-bs-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                applyTheme(newTheme);
            });
        }
    });
</script>

@if(Auth::user()->isAdmin() || Auth::user()->isGerente())
<script>
    document.addEventListener('DOMContentLoaded', function() {
        function revisarAutorizaciones() {
            fetch('{{ route("autorizaciones.notificaciones") }}')
                .then(response => response.json())
                .then(data => {
                    const badge = document.getElementById('badge-autorizaciones');
                    if (data.count > 0) {
                        badge.textContent = data.count;
                        badge.style.display = 'inline-block';
                        // Efecto visual sutil cuando cambia
                        badge.classList.add('animate__animated', 'animate__pulse');
                        setTimeout(() => badge.classList.remove('animate__animated', 'animate__pulse'), 1000);
                    } else {
                        badge.style.display = 'none';
                    }
                })
                .catch(error => console.error('Error al revisar notificaciones:', error));
        }

        // Consultar al cargar la página
        revisarAutorizaciones();
        
        // Consultar cada 15 segundos (15000 milisegundos)
        setInterval(revisarAutorizaciones, 15000);
    });
</script>
@endif

</body>
</html>