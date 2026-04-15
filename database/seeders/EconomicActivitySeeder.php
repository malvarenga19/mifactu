<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EconomicActivity;

class EconomicActivitySeeder extends Seeder
{
    public function run()
    {
        $path = database_path('data/economic_activities.csv');

        if (!file_exists($path)) {
            echo "Archivo no encontrado\n";
            return;
        }

        $file = fopen($path, 'r');

        // 🔹 Leer encabezado (con ;)
        fgetcsv($file, 0, ';');

        while (($row = fgetcsv($file, 0, ';')) !== false) {

            $code = trim($row[0] ?? '');
            $description = trim($row[1] ?? '');

            // 🔥 Validaciones
            if (empty($code)) continue;
            if (!is_numeric($code)) continue;

            EconomicActivity::updateOrCreate(
                ['code' => $code],
                ['description' => $description]
            );
        }

        fclose($file);

        echo "Seeder ejecutado correctamente\n";
    }
}