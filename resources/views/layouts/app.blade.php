<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'FOUNT Contable') - {{ config('app.name', 'Cooperadora') }}</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
    <!-- UI Improvements -->
    <link href="{{ asset('css/ui-improvements.css') }}" rel="stylesheet">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 250px;
            background: #343a40;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .main-content {
            margin-left: 250px;
            transition: all 0.3s ease;
        }

        .navbar-brand {
            color: white !important;
            font-weight: bold;
        }

        .sidebar .nav-link {
            color: #adb5bd;
            padding: 12px 20px;
            border-bottom: 1px solid #495057;
        }

        .sidebar .nav-link:hover {
            color: white;
            background-color: #495057;
        }

        .sidebar .nav-link.active {
            color: white;
            background-color: #007bff;
        }

        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }

        .user-info {
            padding: 20px;
            border-bottom: 1px solid #495057;
            color: white;
        }

        @media (max-width: 768px) {
            .sidebar {
                margin-left: -250px;
            }

            .main-content {
                margin-left: 0;
            }

            .sidebar.show {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="user-info">
            <div class="d-flex align-items-center">
                <i class="fas fa-user-circle fa-2x me-2"></i>
                <div>
                    <div class="fw-bold">{{ Auth::user()->name }}</div>
                    <small class="text-muted">{{ ucfirst(Auth::user()->roles->first()?->name ?? 'Usuario') }}</small>
                </div>
            </div>
        </div>

        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('pos.*') ? 'active' : '' }}" href="{{ route('pos.index') }}">
                    <i class="fas fa-cash-register"></i>
                    Punto de Venta
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">
                    <i class="fas fa-box"></i>
                    Productos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('students.*') ? 'active' : '' }}" href="{{ route('students.index') }}">
                    <i class="fas fa-users"></i>
                    Estudiantes
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" onclick="event.preventDefault();">
                    <i class="fas fa-chart-line"></i>
                    Reportes
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" onclick="event.preventDefault();">
                    <i class="fas fa-cash-register"></i>
                    Caja
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" onclick="event.preventDefault();">
                    <i class="fas fa-history"></i>
                    Auditoría
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" onclick="event.preventDefault();">
                    <i class="fas fa-cog"></i>
                    Configuración
                </a>
            </li>
            <li class="nav-item mt-auto">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <a class="nav-link" href="#" onclick="event.preventDefault(); this.closest('form').submit();">
                        <i class="fas fa-sign-out-alt"></i>
                        Cerrar Sesión
                    </a>
                </form>
            </li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <div class="container-fluid">
                <button class="btn btn-outline-light d-md-none me-2" type="button" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>

                <span class="navbar-brand mb-0 h1">
                    @yield('page-title', 'FOUNT Contable')
                </span>

                <div class="navbar-nav ms-auto">
                    <span class="navbar-text text-white">
                        <i class="fas fa-building"></i>
                        Sucursal: {{ Auth::user()->branch?->name ?? 'Principal' }}
                    </span>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main class="p-4">
            @if(View::hasSection('header'))
                <div class="mb-4">
                    @yield('header')
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <!-- DataTables Configuration -->
    <script src="{{ asset('js/datatables-config.js') }}"></script>

    <script>
        // Sidebar toggle for mobile
        $('#sidebarToggle').click(function() {
            $('.sidebar').toggleClass('show');
        });

        // Close sidebar when clicking outside on mobile
        $(document).click(function(event) {
            if (!$(event.target).closest('.sidebar, #sidebarToggle').length) {
                $('.sidebar').removeClass('show');
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
