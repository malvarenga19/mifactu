<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Facturación — @yield('title')</title>

    <!-- Typefaces -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600&family=Source+Sans+3:wght@300;400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <style>
        /* ─── Design Tokens ──────────────────────────────────────── */
        :root {
            --ink:        #0f0e0c;
            --surface:    #f5f3ef;
            --paper:      #faf9f7;
            --rule:       #dedad3;
            --muted:      #8c8880;
            --accent:     #b8922a;
            --accent-dk:  #8c6b18;
            --accent-lt:  #f0e4c4;
            --danger:     #9b3535;
            --success:    #2d6a4f;
            --sidebar-w:  240px;
            --header-h:   60px;
            --radius:     3px;
            --mono:       'JetBrains Mono', monospace;
            --serif:      'Playfair Display', Georgia, serif;
            --sans:       'Source Sans 3', 'Segoe UI', sans-serif;
            --shadow-sm:  0 1px 4px rgba(15,14,12,.08);
            --shadow-md:  0 4px 16px rgba(15,14,12,.12);
        }

        /* ─── Reset & Base ───────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; }

        html, body {
            height: 100%;
            margin: 0;
            background: var(--surface);
            color: var(--ink);
            font-family: var(--sans);
            font-size: 15px;
            font-weight: 400;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        /* ─── Layout Shell ───────────────────────────────────────── */
        .app-shell {
            display: grid;
            grid-template-columns: var(--sidebar-w) 1fr;
            grid-template-rows: var(--header-h) 1fr;
            min-height: 100vh;
        }

        /* ─── Top Bar ────────────────────────────────────────────── */
        .topbar {
            grid-column: 1 / -1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--ink);
            padding: 0 28px;
            position: sticky;
            top: 0;
            z-index: 200;
            border-bottom: 1px solid #222;
        }

        .topbar__brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .topbar__logo {
            width: 30px;
            height: 30px;
            background: var(--accent);
            border-radius: 2px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            color: #fff;
            flex-shrink: 0;
        }

        .topbar__name {
            font-family: var(--serif);
            font-weight: 500;
            font-size: 1.05rem;
            color: #fff;
            letter-spacing: .01em;
        }

        .topbar__name span {
            color: var(--accent);
        }

        .topbar__right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .topbar__pill {
            font-size: 11px;
            font-family: var(--mono);
            font-weight: 500;
            letter-spacing: .05em;
            text-transform: uppercase;
            color: var(--muted);
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 2px;
            padding: 3px 10px;
        }

        .topbar__hamburger {
            display: none;
            background: none;
            border: none;
            color: var(--muted);
            font-size: 18px;
            cursor: pointer;
            padding: 4px;
        }

        /* ─── Sidebar ────────────────────────────────────────────── */
        .sidebar {
            background: var(--paper);
            border-right: 1px solid var(--rule);
            display: flex;
            flex-direction: column;
            padding: 32px 0 24px;
            position: sticky;
            top: var(--header-h);
            height: calc(100vh - var(--header-h));
            overflow-y: auto;
        }

        .nav-section-label {
            font-family: var(--mono);
            font-size: 10px;
            font-weight: 500;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--muted);
            padding: 0 20px;
            margin: 20px 0 6px;
        }

        .nav-section-label:first-child {
            margin-top: 0;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 20px;
            font-size: .875rem;
            font-weight: 400;
            color: #4a4844;
            text-decoration: none;
            border-left: 2px solid transparent;
            transition: all .15s ease;
        }

        .sidebar-link i {
            width: 16px;
            font-size: 13px;
            color: var(--muted);
            flex-shrink: 0;
            transition: color .15s;
        }

        .sidebar-link:hover {
            color: var(--ink);
            background: rgba(184,146,42,.06);
            border-left-color: var(--accent);
        }

        .sidebar-link:hover i {
            color: var(--accent);
        }

        .sidebar-link.active {
            color: var(--ink);
            font-weight: 500;
            background: var(--accent-lt);
            border-left-color: var(--accent);
        }

        .sidebar-link.active i {
            color: var(--accent-dk);
        }

        .sidebar-divider {
            margin: 12px 20px;
            border: none;
            border-top: 1px solid var(--rule);
        }

        /* ─── Main Content ───────────────────────────────────────── */
        .main-content {
            padding: 36px 36px 60px;
            overflow-y: auto;
            background: var(--surface);
        }

        /* ─── Page Header ────────────────────────────────────────── */
        .page-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            margin-bottom: 28px;
            padding-bottom: 18px;
            border-bottom: 1px solid var(--rule);
        }

        .page-title {
            font-family: var(--serif);
            font-size: 1.6rem;
            font-weight: 500;
            color: var(--ink);
            margin: 0;
            line-height: 1.2;
        }

        .page-subtitle {
            font-size: .8rem;
            color: var(--muted);
            margin: 4px 0 0;
            font-family: var(--mono);
            letter-spacing: .04em;
        }

        /* ─── Alerts ─────────────────────────────────────────────── */
        .alert {
            border-radius: var(--radius);
            border: 1px solid transparent;
            font-size: .875rem;
            padding: 12px 16px;
        }

        .alert-success {
            background: #edf7f2;
            border-color: #a8d5be;
            color: var(--success);
        }

        .alert-danger {
            background: #fdf0f0;
            border-color: #e8b8b8;
            color: var(--danger);
        }

        /* ─── Cards ──────────────────────────────────────────────── */
        .card {
            background: var(--paper);
            border: 1px solid var(--rule);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid var(--rule);
            padding: 16px 20px;
            font-size: .8rem;
            font-family: var(--mono);
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--muted);
            font-weight: 500;
        }

        .card-body { padding: 20px; }

        /* ─── Tables ─────────────────────────────────────────────── */
        .table {
            font-size: .875rem;
            color: var(--ink);
        }

        .table thead th {
            font-family: var(--mono);
            font-size: .7rem;
            font-weight: 500;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--muted);
            border-bottom: 1px solid var(--rule);
            border-top: none;
            padding: 10px 14px;
            background: transparent;
        }

        .table tbody td {
            padding: 11px 14px;
            border-bottom: 1px solid #ede9e3;
            vertical-align: middle;
        }

        .table-hover tbody tr:hover {
            cursor: pointer;
            background: rgba(184,146,42,.04);
        }

        /* ─── Buttons ────────────────────────────────────────────── */
        .btn {
            font-family: var(--sans);
            font-size: .8rem;
            font-weight: 500;
            letter-spacing: .02em;
            border-radius: var(--radius);
            padding: 8px 18px;
            transition: all .15s;
            border-width: 1px;
        }

        .btn-primary {
            background: var(--accent);
            border-color: var(--accent);
            color: #fff;
        }

        .btn-primary:hover {
            background: var(--accent-dk);
            border-color: var(--accent-dk);
            color: #fff;
        }

        .btn-outline-primary {
            background: transparent;
            border-color: var(--accent);
            color: var(--accent);
        }

        .btn-outline-primary:hover {
            background: var(--accent-lt);
            border-color: var(--accent);
            color: var(--accent-dk);
        }

        .btn-outline-secondary {
            background: transparent;
            border-color: var(--rule);
            color: var(--muted);
        }

        .btn-outline-secondary:hover {
            background: var(--surface);
            border-color: #c8c4bc;
            color: var(--ink);
        }

        .btn-sm {
            padding: 5px 12px;
            font-size: .75rem;
        }

        /* ─── Forms ──────────────────────────────────────────────── */
        .form-label {
            font-family: var(--mono);
            font-size: .7rem;
            font-weight: 500;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 6px;
        }

        .form-control, .form-select {
            border: 1px solid var(--rule);
            border-radius: var(--radius);
            font-family: var(--sans);
            font-size: .875rem;
            color: var(--ink);
            background: var(--paper);
            padding: 8px 12px;
            transition: border-color .15s, box-shadow .15s;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(184,146,42,.12);
            outline: none;
            background: #fff;
        }

        /* ─── Badges ─────────────────────────────────────────────── */
        .badge-status {
            font-family: var(--mono);
            font-size: .65rem;
            font-weight: 500;
            letter-spacing: .08em;
            text-transform: uppercase;
            padding: 4px 9px;
            border-radius: 2px;
        }

        .badge-status.pending   { background: #f5ead1; color: #7a5c10; }
        .badge-status.paid      { background: #d8f0e6; color: #236348; }
        .badge-status.cancelled { background: #fde8e8; color: #8b2e2e; }

        /* ─── Product Rows ────────────────────────────────────────── */
        .product-row {
            background: var(--surface);
            border: 1px solid var(--rule);
            border-radius: var(--radius);
            padding: 14px 16px;
            margin-bottom: 10px;
        }

        .btn-remove-product {
            margin-top: 29px;
        }

        /* ─── Summary Card ───────────────────────────────────────── */
        .summary-card {
            background: var(--ink);
            color: #e8e4dc;
            border-radius: var(--radius);
            padding: 24px;
        }

        .summary-card .summary-title {
            font-family: var(--serif);
            font-size: 1rem;
            font-weight: 500;
            color: var(--accent);
            margin-bottom: 18px;
            letter-spacing: .02em;
        }

        .summary-card .form-label {
            color: rgba(232,228,220,.55);
        }

        .summary-card .form-control {
            background: rgba(255,255,255,.06);
            border-color: rgba(255,255,255,.12);
            color: #e8e4dc;
            font-family: var(--mono);
        }

        .summary-card .form-control:focus {
            background: rgba(255,255,255,.1);
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(184,146,42,.2);
            color: #fff;
        }

        .summary-total {
            font-family: var(--mono);
            font-size: 1.6rem;
            font-weight: 500;
            color: #fff;
            letter-spacing: .02em;
            margin-top: 4px;
        }

        /* ─── Modals ─────────────────────────────────────────────── */
        .modal-content {
            border: 1px solid var(--rule);
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
        }

        .modal-header {
            background: var(--paper);
            border-bottom: 1px solid var(--rule);
            padding: 16px 24px;
        }

        .modal-title {
            font-family: var(--serif);
            font-weight: 500;
            font-size: 1.15rem;
            color: var(--ink);
        }

        .modal-body { padding: 24px; background: var(--paper); }
        .modal-footer {
            background: var(--surface);
            border-top: 1px solid var(--rule);
            padding: 14px 24px;
        }

        .modal-lg { max-width: 900px; }

        /* ─── DataTables overrides ───────────────────────────────── */
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid var(--rule);
            border-radius: var(--radius);
            font-family: var(--sans);
            font-size: .8rem;
            padding: 5px 10px;
            color: var(--ink);
            background: var(--paper);
        }

        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_length label,
        .dataTables_wrapper .dataTables_filter label {
            font-size: .78rem;
            color: var(--muted);
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            font-size: .78rem;
            border-radius: var(--radius) !important;
            font-family: var(--mono);
        }

        /* ─── Responsive ─────────────────────────────────────────── */
        @media (max-width: 768px) {
            .app-shell {
                grid-template-columns: 1fr;
                grid-template-rows: var(--header-h) auto 1fr;
            }

            .sidebar {
                display: none;
                position: fixed;
                top: var(--header-h);
                left: 0;
                width: 260px;
                height: calc(100vh - var(--header-h));
                z-index: 300;
                box-shadow: var(--shadow-md);
            }

            .sidebar.open { display: flex; }

            .topbar__hamburger { display: block; }

            .main-content { padding: 24px 16px 48px; }
        }

        /* ─── Print ──────────────────────────────────────────────── */
        @media print {
            .no-print { display: none !important; }

            .app-shell {
                grid-template-columns: 1fr;
            }

            .main-content {
                padding: 0;
                background: #fff;
            }
        }

        /* ─── Utilities ──────────────────────────────────────────── */
        .mono { font-family: var(--mono); }
        .text-accent { color: var(--accent); }
        .text-muted  { color: var(--muted) !important; }
    </style>

    @stack('styles')
</head>
<body>
<div class="app-shell">

    <!-- ── Top Bar ─────────────────────────────────────────────── -->
    <header class="topbar no-print">
        <a class="topbar__brand" href="{{ route('invoices.index') }}">
            <div class="topbar__logo">
                <i class="fas fa-file-invoice-dollar"></i>
            </div>
            <span class="topbar__name">Sistema de <span>Facturación</span></span>
        </a>

        <div class="topbar__right">
            <span class="topbar__pill">{{ now()->format('d M Y') }}</span>
            <button class="topbar__hamburger" id="sidebarToggle" aria-label="Menú">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </header>

    <!-- ── Sidebar ─────────────────────────────────────────────── -->
    <nav class="sidebar no-print" id="sidebar">
        <span class="nav-section-label">Operaciones</span>
        <a class="sidebar-link {{ request()->routeIs('invoices.index') ? 'active' : '' }}"
           href="{{ route('invoices.index') }}">
            <i class="fas fa-list-ul"></i> Listado de Facturas
        </a>
        <a class="sidebar-link {{ request()->routeIs('invoices.create') ? 'active' : '' }}"
           href="{{ route('invoices.create') }}">
            <i class="fas fa-plus-circle"></i> Nueva Factura
        </a>

        <hr class="sidebar-divider">

        <span class="nav-section-label">Mantenimiento</span>
        <a class="sidebar-link {{ request()->routeIs('customers.*') ? 'active' : '' }}"
           href="{{ route('customers.index') }}">
            <i class="fas fa-users"></i> Clientes
        </a>
        <a class="sidebar-link {{ request()->routeIs('products.*') ? 'active' : '' }}"
           href="{{ route('products.index') }}">
            <i class="fas fa-box-archive"></i> Productos
        </a>
        <a class="sidebar-link {{ request()->routeIs('inventory_movements.*') ? 'active' : '' }}"
           href="{{ route('inventory_movements.index') }}">
            <i class="fas fa-box-archive"></i> Inventario
        </a>
        <a class="sidebar-link"
           href="{{ route('invoices.index') }}">
            <i class="fas fa-tags"></i> Tipos de Factura
        </a>
    </nav>

    <!-- ── Main ────────────────────────────────────────────────── -->
    <main class="main-content">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <i class="fas fa-triangle-exclamation me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        @yield('content')

    </main>

</div><!-- .app-shell -->

<!-- ── Scripts ──────────────────────────────────────────────────── -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    // CSRF global
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // Formato moneda
    function formatMoney(amount) {
        return new Intl.NumberFormat('es-SV', {
            style: 'currency',
            currency: 'USD',
            minimumFractionDigits: 2
        }).format(amount);
    }

    // Loading helpers
    function showLoading() { $('#loadingOverlay').fadeIn(); }
    function hideLoading()  { $('#loadingOverlay').fadeOut(); }

    // Sidebar móvil
    document.getElementById('sidebarToggle').addEventListener('click', function () {
        document.getElementById('sidebar').classList.toggle('open');
    });
</script>

@stack('scripts')
</body>
</html>