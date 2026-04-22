<?php

namespace Database\Seeders;

use App\Models\InvoiceType;
use Illuminate\Database\Seeder;

class InvoiceTypeSeeder extends Seeder
{
    public function run()
    {
        $types = [
            ['code' => '01', 'name' => 'Consumidor Final', 'last_correlative' => 0],
            ['code' => '03', 'name' => 'Comprobante de Crédito Fiscal', 'last_correlative' => 0],
            ['code' => '05', 'name' => 'Nota de Crédito', 'last_correlative' => 0],
            ['code' => '11', 'name' => 'Factura de Exportación', 'last_correlative' => 0],
            ['code' => '14', 'name' => 'Factura de Sujeto Excluido', 'last_correlative' => 0],
        ];
        
        foreach ($types as $type) {
            InvoiceType::create($type);
        }
    }
}