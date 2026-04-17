<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistema de Clientes')</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome (íconos) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Estilos personalizados -->
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #2c3e50;
        }

        .sidebar a {
            color: #ecf0f1;
            text-decoration: none;
            padding: 10px 15px;
            display: block;
            transition: 0.3s;
        }

        .sidebar a:hover {
            background-color: #34495e;
        }

        .sidebar i {
            margin-right: 10px;
        }

        .content {
            background-color: #f8f9fa;
            min-height: 100vh;
        }

        .navbar-brand {
            font-weight: bold;
        }
    </style>


    @stack('styles')
    @vite('resources/css/app.css')
</head>

<body>
    <div class="container-fluid p-0">
        <div class="row g-0">
            <!-- Sidebar -->
            <div class="col-auto">
                <div class="sidebar p-3" style="width: 250px;">
                    <h4 class="text-white mb-4">Mi Sistema</h4>
                    <a href="{{ route('customers.index') }}">
                        <i class="fas fa-users"></i> Clientes
                    </a>
                    <a href="{{ route('suppliers.index') }}">
                        <i class="fas fa-truck"></i> Proveedores
                    </a>
                    <a href="{{ route('categories.index') }}">
                        <i class="fas fa-tags"></i> Categorías
                    </a>
                    <a href="{{ route('products.index') }}">
                        <i class="fas fa-box"></i> Productos
                    </a>
                    <a href="{{ route('inventory_movements.index') }}">
                        <i class="fas fa-box"></i> Movimientos de Inventario
                    </a>
                    <a href="#">
                        <i class="fas fa-chart-line"></i> Dashboard
                    </a>
                    <a href="#">
                        <i class="fas fa-cog"></i> Configuración
                    </a>
                </div>
            </div>

            <!-- Contenido principal -->
            <div class="col">
                <!-- Navbar -->
                <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
                    <div class="container-fluid">
                        <span class="navbar-brand">Bienvenido</span>
                        <div class="ms-auto">
                            <span class="text-muted">Usuario</span>
                        </div>
                    </div>
                </nav>

                <!-- Contenido -->
                <div class="content p-4">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery (opcional, para selects dinámicos) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    @stack('scripts')
</body>

</html>