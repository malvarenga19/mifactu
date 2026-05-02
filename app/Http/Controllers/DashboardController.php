<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $hoy        = Carbon::today();
        $inicioMes  = $hoy->copy()->startOfMonth();
        $finMes     = $hoy->copy()->endOfMonth();
        $mesAnterior = $hoy->copy()->subMonth()->startOfMonth();
        $finMesAnterior = $hoy->copy()->subMonth()->endOfMonth();

        // ── Ventas hoy ──────────────────────────────────────────────
        $ventasHoy = DB::table('invoices')
            ->whereDate('issue_date', $hoy)
            ->whereNotIn('status', ['cancelled'])
            ->sum('total_amount');

        // ── Ventas mes actual ────────────────────────────────────────
        $ventasMes = DB::table('invoices')
            ->whereBetween('issue_date', [$inicioMes, $finMes])
            ->whereNotIn('status', ['cancelled'])
            ->sum('total_amount');

        // ── Ventas mes anterior (para comparar) ─────────────────────
        $ventasMesAnterior = DB::table('invoices')
            ->whereBetween('issue_date', [$mesAnterior, $finMesAnterior])
            ->whereNotIn('status', ['cancelled'])
            ->sum('total_amount');

        $variacionMes = $ventasMesAnterior > 0
            ? round((($ventasMes - $ventasMesAnterior) / $ventasMesAnterior) * 100, 1)
            : null;

        // ── Facturas pendientes de pago ──────────────────────────────
        $facturasPendientes = DB::table('invoices')
            ->where('payment_status', 'pending')
            ->whereNotIn('status', ['cancelled'])
            ->count();

        $montoPendiente = DB::table('invoices')
            ->where('payment_status', 'pending')
            ->whereNotIn('status', ['cancelled'])
            ->sum('total_amount');

        $facturasVencidas = DB::table('invoices')
            ->where('payment_status', 'overdue')
            ->whereNotIn('status', ['cancelled'])
            ->count();

        $montoVencido = DB::table('invoices')
            ->where('payment_status', 'overdue')
            ->whereNotIn('status', ['cancelled'])
            ->sum('total_amount');

        // ── Stock bajo / agotado ─────────────────────────────────────
        $productosAgotados = DB::table('products')
            ->where('stock', '<=', 0)
            ->count();

        $productosBajoStock = DB::table('products')
            ->whereRaw('stock > 0 AND stock <= COALESCE(NULLIF(min_stock, 0), 5)')
            ->count();

        $listaStockBajo = DB::table('products')
            ->whereRaw('stock <= COALESCE(NULLIF(min_stock, 0), 5)')
            ->orderBy('stock')
            ->limit(8)
            ->get(['id', 'name', 'code', 'stock', 'min_stock']);

        // ── Productos más vendidos (mes actual) ──────────────────────
        $topProductos = DB::table('invoice_items')
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->join('products', 'products.id', '=', 'invoice_items.product_id')
            ->whereBetween('invoices.issue_date', [$inicioMes, $finMes])
            ->whereNotIn('invoices.status', ['cancelled'])
            ->groupBy('invoice_items.product_id', 'products.name', 'products.code')
            ->orderByDesc('total_vendido')
            ->limit(6)
            ->get([
                'products.name',
                'products.code',
                DB::raw('SUM(invoice_items.quantity) as unidades'),
                DB::raw('SUM(invoice_items.total) as total_vendido'),
            ]);

        // ── Ventas diarias del mes (para gráfica de línea) ──────────
        $ventasDiarias = DB::table('invoices')
            ->whereBetween('issue_date', [$inicioMes, $finMes])
            ->whereNotIn('status', ['cancelled'])
            ->groupBy('issue_date')
            ->orderBy('issue_date')
            ->get([
                'issue_date',
                DB::raw('SUM(total_amount) as total'),
                DB::raw('COUNT(*) as cantidad'),
            ])
            ->keyBy('issue_date');

        // Rellenar todos los días del mes aunque no haya ventas
        $diasMes = [];
        $period  = new \DatePeriod($inicioMes, new \DateInterval('P1D'), $finMes->copy()->addDay());
        foreach ($period as $dia) {
            $key = $dia->format('Y-m-d');
            $diasMes[] = [
                'fecha'    => $dia->format('d'),
                'total'    => isset($ventasDiarias[$key]) ? (float) $ventasDiarias[$key]->total : 0,
                'cantidad' => isset($ventasDiarias[$key]) ? (int) $ventasDiarias[$key]->cantidad : 0,
            ];
        }

        // ── Últimas 5 facturas ───────────────────────────────────────
        $ultimasFacturas = DB::table('invoices')
            ->join('customers', 'customers.id', '=', 'invoices.customer_id')
            ->join('invoice_types', 'invoice_types.id', '=', 'invoices.invoice_type_id')
            ->orderByDesc('invoices.id')
            ->limit(5)
            ->get([
                'invoices.id',
                'invoices.correlative',
                'invoices.issue_date',
                'invoices.total_amount',
                'invoices.status',
                'invoices.payment_status',
                'customers.name as cliente',
                'invoice_types.name as tipo',
            ]);

        return view('dashboard', compact(
            'ventasHoy',
            'ventasMes',
            'ventasMesAnterior',
            'variacionMes',
            'facturasPendientes',
            'montoPendiente',
            'facturasVencidas',
            'montoVencido',
            'productosAgotados',
            'productosBajoStock',
            'listaStockBajo',
            'topProductos',
            'diasMes',
            'ultimasFacturas',
        ));
    }
}