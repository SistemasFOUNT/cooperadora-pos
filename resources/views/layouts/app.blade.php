<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Sistema POS Cooperadora')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        .sidebar {
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            background: #343a40;
            padding-top: 60px;
            transition: all 0.3s;
        }

        .sidebar a {
            color: #adb5bd;
            padding: 15px 20px;
            text-decoration: none;
            display: block;
            transition: all 0.3s;
        }

        .sidebar a:hover, .sidebar a.active {
            background: #495057;
            color: white;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
            min-height: 100vh;
        }

        .navbar-brand {
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            z-index: 1000;
            background: #212529;
            padding: 15px 20px;
        }

        .pos-item {
            cursor: pointer;
            border: 1px solid #dee2e6;
            transition: all 0.2s;
        }

        .pos-item:hover {
            border-color: #007bff;
            box-shadow: 0 2px 4px rgba(0,123,255,.15);
        }

        .cart-item {
            border-bottom: 1px solid #dee2e6;
            padding: 10px 0;
        }

        .btn-pos {
            font-size: 1.1rem;
            padding: 12px 20px;
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

    @stack('styles')
</head>
<body>
    <!-- Navigation -->
    <div class="navbar-brand text-white">
        <i class="fas fa-store me-2"></i>
        <strong>POS Cooperadora</strong>
    </div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <nav class="nav flex-column">
            <a href="{{ route('pos.index') }}" class="nav-link {{ request()->routeIs('pos.*') ? 'active' : '' }}">
                <i class="fas fa-cash-register me-2"></i> Punto de Venta
            </a>
            <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                <i class="fas fa-box me-2"></i> Productos
            </a>
            <a href="{{ route('students.index') }}" class="nav-link {{ request()->routeIs('students.*') ? 'active' : '' }}">
                <i class="fas fa-graduation-cap me-2"></i> Estudiantes
            </a>
            <a href="{{ route('sales.index') }}" class="nav-link {{ request()->routeIs('sales.*') ? 'active' : '' }}">
                <i class="fas fa-receipt me-2"></i> Ventas
            </a>
            <a href="{{ route('cash.index') }}" class="nav-link {{ request()->routeIs('cash.*') ? 'active' : '' }}">
                <i class="fas fa-money-bill me-2"></i> Caja
            </a>
            <div class="nav-divider my-3 border-top"></div>
            <a href="#" class="nav-link">
                <i class="fas fa-chart-bar me-2"></i> Reportes
            </a>
            <a href="#" class="nav-link">
                <i class="fas fa-cog me-2"></i> Configuración
            </a>
            <div class="nav-divider my-3 border-top"></div>
            <a href="#" class="nav-link text-danger" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
            </a>
        </nav>

        <!-- User Info -->
        @auth
        <div class="position-absolute bottom-0 w-100 p-3 text-white bg-dark">
            <div class="d-flex align-items-center">
                <i class="fas fa-user-circle fa-2x me-2"></i>
                <div>
                    <div class="fw-bold">{{ Auth::user()->name }}</div>
                    <small class="text-muted">{{ Auth::user()->branch?->name }}</small>
                </div>
            </div>
        </div>
        @endauth
    </div>

    <!-- Main Content -->
    <div class="main-content">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>

    <!-- Logout Form -->
    <form id="logout-form" action="#" method="POST" style="display: none;">
        @csrf
    </form>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    @stack('scripts')
</body>
</html>
