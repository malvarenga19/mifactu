<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'MiFactu') — Facturación</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg:        #0f0f13;
            --surface:   #18181f;
            --surface2:  #22222c;
            --border:    #2e2e3a;
            --accent:    #e8c547;
            --accent2:   #4ecdc4;
            --danger:    #ff5c5c;
            --success:   #6bcb77;
            --warn:      #ffa552;
            --text:      #e8e8f0;
            --muted:     #7a7a9a;
            --radius:    6px;
            --mono:      'Space Mono', monospace;
            --sans:      'DM Sans', sans-serif;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { font-size: 15px; }
        body {
            background: var(--bg);
            color: var(--text);
            font-family: var(--sans);
            min-height: 100vh;
            display: flex;
        }

        /* ── Sidebar ── */
        .sidebar {
            width: 220px;
            min-height: 100vh;
            background: var(--surface);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            padding: 0;
            position: sticky;
            top: 0;
            height: 100vh;
        }
        .sidebar-logo {
            padding: 1.5rem 1.4rem 1.2rem;
            border-bottom: 1px solid var(--border);
        }
        .sidebar-logo span {
            font-family: var(--mono);
            font-size: 1.1rem;
            color: var(--accent);
            letter-spacing: 0.04em;
        }
        .sidebar-logo small {
            display: block;
            color: var(--muted);
            font-size: 0.7rem;
            margin-top: 2px;
        }
        .nav-section {
            padding: 1rem 0.8rem 0.4rem;
            font-size: 0.65rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--muted);
            font-family: var(--mono);
        }
        .sidebar nav a {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.55rem 1.4rem;
            color: var(--muted);
            text-decoration: none;
            font-size: 0.88rem;
            transition: color 0.15s, background 0.15s;
            border-left: 2px solid transparent;
        }
        .sidebar nav a:hover,
        .sidebar nav a.active {
            color: var(--text);
            background: var(--surface2);
            border-left-color: var(--accent);
        }
        .sidebar nav a .icon { font-size: 1rem; width: 18px; text-align: center; }

        /* ── Main ── */
        .main-wrap {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            overflow: hidden;
        }
        .topbar {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 0.85rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }
        .topbar h1 {
            font-family: var(--mono);
            font-size: 0.9rem;
            color: var(--muted);
            font-weight: 400;
            letter-spacing: 0.05em;
        }
        .topbar h1 strong { color: var(--text); font-weight: 700; }
        .content {
            padding: 2rem;
            flex: 1;
        }

        /* ── Alerts ── */
        .alert {
            padding: 0.75rem 1.1rem;
            border-radius: var(--radius);
            margin-bottom: 1.5rem;
            font-size: 0.88rem;
            border-left: 3px solid;
        }
        .alert-success { background: rgba(107,203,119,0.08); border-color: var(--success); color: var(--success); }
        .alert-error   { background: rgba(255,92,92,0.08);   border-color: var(--danger);  color: var(--danger); }

        /* ── Buttons ── */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.5rem 1.1rem;
            border-radius: var(--radius);
            font-family: var(--sans);
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: opacity 0.15s, transform 0.1s;
        }
        .btn:active { transform: scale(0.97); }
        .btn-primary { background: var(--accent); color: #0f0f13; }
        .btn-primary:hover { opacity: 0.88; }
        .btn-secondary { background: var(--surface2); color: var(--text); border: 1px solid var(--border); }
        .btn-secondary:hover { border-color: var(--muted); }
        .btn-danger { background: rgba(255,92,92,0.12); color: var(--danger); border: 1px solid rgba(255,92,92,0.3); }
        .btn-danger:hover { background: rgba(255,92,92,0.2); }
        .btn-sm { padding: 0.3rem 0.75rem; font-size: 0.78rem; }

        /* ── Cards ── */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.5rem;
        }
        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.4rem;
        }
        .card-title {
            font-family: var(--mono);
            font-size: 0.88rem;
            color: var(--accent);
            letter-spacing: 0.05em;
        }

        /* ── Table ── */
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 0.86rem; }
        thead th {
            font-family: var(--mono);
            font-size: 0.7rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--muted);
            padding: 0.6rem 1rem;
            border-bottom: 1px solid var(--border);
            text-align: left;
        }
        tbody td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--border);
            color: var(--text);
        }
        tbody tr:hover { background: var(--surface2); }
        tbody tr:last-child td { border-bottom: none; }

        /* ── Badges ── */
        .badge {
            display: inline-block;
            padding: 0.18rem 0.6rem;
            border-radius: 20px;
            font-size: 0.72rem;
            font-family: var(--mono);
            font-weight: 700;
            letter-spacing: 0.04em;
        }
        .badge-draft    { background: rgba(122,122,154,0.15); color: var(--muted); }
        .badge-issued   { background: rgba(78,205,196,0.12);  color: var(--accent2); }
        .badge-cancelled{ background: rgba(255,92,92,0.12);   color: var(--danger); }
        .badge-pending  { background: rgba(255,165,82,0.12);  color: var(--warn); }
        .badge-paid     { background: rgba(107,203,119,0.12); color: var(--success); }
        .badge-overdue  { background: rgba(255,92,92,0.12);   color: var(--danger); }

        /* ── Forms ── */
        .form-group { margin-bottom: 1.1rem; }
        label { display: block; font-size: 0.78rem; color: var(--muted); margin-bottom: 0.35rem; font-family: var(--mono); letter-spacing: 0.06em; text-transform: uppercase; }
        input[type=text], input[type=date], input[type=number], select, textarea {
            width: 100%;
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            color: var(--text);
            padding: 0.55rem 0.8rem;
            font-family: var(--sans);
            font-size: 0.88rem;
            transition: border-color 0.15s;
            outline: none;
        }
        input:focus, select:focus, textarea:focus { border-color: var(--accent); }
        textarea { resize: vertical; min-height: 80px; }
        .form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; }
        .invalid-feedback { color: var(--danger); font-size: 0.78rem; margin-top: 0.25rem; }

        /* ── Pagination ── */
        .pagination { display: flex; gap: 0.3rem; justify-content: flex-end; margin-top: 1.2rem; }
        .pagination a, .pagination span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 32px;
            height: 32px;
            padding: 0 0.4rem;
            border-radius: var(--radius);
            font-size: 0.8rem;
            text-decoration: none;
            border: 1px solid var(--border);
            color: var(--muted);
            background: var(--surface2);
        }
        .pagination .active, .pagination a:hover { border-color: var(--accent); color: var(--accent); }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <style>
        /* ── Select2 dark theme override ── */
        .select2-container--default .select2-selection--single {
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            height: 36px;
            display: flex;
            align-items: center;
            transition: border-color .15s;
        }
        .select2-container--default.select2-container--focus .select2-selection--single,
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: var(--accent);
            outline: none;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: var(--text);
            font-family: var(--sans);
            font-size: .88rem;
            line-height: 34px;
            padding-left: .8rem;
        }
        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: var(--muted);
        }
        .select2-container--default .select2-selection__arrow {
            height: 34px;
            right: 6px;
        }
        .select2-container--default .select2-selection__arrow b {
            border-color: var(--muted) transparent transparent;
        }
        .select2-container--default.select2-container--open .select2-selection__arrow b {
            border-color: transparent transparent var(--accent);
        }
        .select2-dropdown {
            background: var(--surface2);
            border: 1px solid var(--accent);
            border-radius: var(--radius);
            box-shadow: 0 8px 24px rgba(0,0,0,.5);
        }
        .select2-search--dropdown {
            padding: .5rem;
            border-bottom: 1px solid var(--border);
        }
        .select2-container--default .select2-search--dropdown .select2-search__field {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            color: var(--text);
            font-family: var(--sans);
            font-size: .85rem;
            padding: .35rem .6rem;
            outline: none;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field:focus {
            border-color: var(--accent);
        }
        .select2-results__option {
            padding: .5rem .9rem;
            font-size: .86rem;
            color: var(--text);
            font-family: var(--sans);
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background: var(--accent);
            color: #0f0f13;
        }
        .select2-container--default .select2-results__option[aria-selected=true] {
            background: var(--surface);
            color: var(--accent);
        }
        .select2-results__option--group { color: var(--muted); font-size: .75rem; }
    </style>
    @stack('styles')
</head>
<body>
<aside class="sidebar">
    <div class="sidebar-logo">
        <span>MiFactu</span>
        <small>Sistema de facturación</small>
    </div>
    <div class="nav-section">Principal</div>
    <nav>
        <a href="{{ route('invoices.index') }}" class="{{ request()->routeIs('invoices.*') ? 'active' : '' }}">
            <span class="icon">◈</span> Facturas
        </a>
        <a href="{{ route('customers.index') }}" class="{{ request()->routeIs('customers.*') ? 'active' : '' }}">
            <span class="icon">◇</span> Clientes
        </a>
        <a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.*') ? 'active' : '' }}">
            <span class="icon">◉</span> Productos
        </a>
    </nav>
    <div class="nav-section">Sistema</div>
    <nav>
        <a href="#">
            <span class="icon">◎</span> Configuración
        </a>
    </nav>
</aside>

<div class="main-wrap">
    <div class="topbar">
        <h1>{!! trim($__env->yieldContent('breadcrumb', '<strong>Dashboard</strong>')) !!}</h1>
        <div>@yield('topbar-actions')</div>
    </div>
    <div class="content">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif
        @yield('content')
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
@stack('scripts')
</body>
</html>