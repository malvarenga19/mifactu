<?php

namespace Database\Seeders; 
use Illuminate\Database\Seeder;
use App\Models\Municipality;

class MunicipalitySeeder extends Seeder
{
    public function run()
    {
        $data = [

            ['code'=>'00','name'=>'Otro (Para extranjeros)','department_id'=>1],

            ['code'=>'13','name'=>'AHUACHAPAN NORTE','department_id'=>2],
            ['code'=>'14','name'=>'AHUACHAPAN CENTRO','department_id'=>2],
            ['code'=>'15','name'=>'AHUACHAPAN SUR','department_id'=>2],

            ['code'=>'14','name'=>'SANTA ANA NORTE','department_id'=>3],
            ['code'=>'15','name'=>'SANTA ANA CENTRO','department_id'=>3],
            ['code'=>'16','name'=>'SANTA ANA ESTE','department_id'=>3],
            ['code'=>'17','name'=>'SANTA ANA OESTE','department_id'=>3],

            ['code'=>'17','name'=>'SONSONATE NORTE','department_id'=>4],
            ['code'=>'18','name'=>'SONSONATE CENTRO','department_id'=>4],
            ['code'=>'19','name'=>'SONSONATE ESTE','department_id'=>4],
            ['code'=>'20','name'=>'SONSONATE OESTE','department_id'=>4],

            ['code'=>'34','name'=>'CHALATENANGO NORTE','department_id'=>5],
            ['code'=>'35','name'=>'CHALATENANGO CENTRO','department_id'=>5],
            ['code'=>'36','name'=>'CHALATENANGO SUR','department_id'=>5],

            ['code'=>'23','name'=>'LA LIBERTAD NORTE','department_id'=>6],
            ['code'=>'24','name'=>'LA LIBERTAD CENTRO','department_id'=>6],
            ['code'=>'25','name'=>'LA LIBERTAD OESTE','department_id'=>6],
            ['code'=>'26','name'=>'LA LIBERTAD ESTE','department_id'=>6],
            ['code'=>'27','name'=>'LA LIBERTAD COSTA','department_id'=>6],
            ['code'=>'28','name'=>'LA LIBERTAD SUR','department_id'=>6],

            ['code'=>'20','name'=>'SAN SALVADOR NORTE','department_id'=>7],
            ['code'=>'21','name'=>'SAN SALVADOR OESTE','department_id'=>7],
            ['code'=>'22','name'=>'SAN SALVADOR ESTE','department_id'=>7],
            ['code'=>'23','name'=>'SAN SALVADOR CENTRO','department_id'=>7],
            ['code'=>'24','name'=>'SAN SALVADOR SUR','department_id'=>7],

            ['code'=>'17','name'=>'CUSCATLAN NORTE','department_id'=>8],
            ['code'=>'18','name'=>'CUSCATLAN SUR','department_id'=>8],

            ['code'=>'23','name'=>'LA PAZ OESTE','department_id'=>9],
            ['code'=>'24','name'=>'LA PAZ CENTRO','department_id'=>9],
            ['code'=>'25','name'=>'LA PAZ ESTE','department_id'=>9],

            ['code'=>'10','name'=>'CABAÑAS OESTE','department_id'=>10],
            ['code'=>'11','name'=>'CABAÑAS ESTE','department_id'=>10],

            ['code'=>'14','name'=>'SAN VICENTE NORTE','department_id'=>11],
            ['code'=>'15','name'=>'SAN VICENTE SUR','department_id'=>11],

            ['code'=>'24','name'=>'USULUTAN NORTE','department_id'=>12],
            ['code'=>'25','name'=>'USULUTAN ESTE','department_id'=>12],
            ['code'=>'26','name'=>'USULUTAN OESTE','department_id'=>12],

            ['code'=>'21','name'=>'SAN MIGUEL NORTE','department_id'=>13],
            ['code'=>'22','name'=>'SAN MIGUEL CENTRO','department_id'=>13],
            ['code'=>'23','name'=>'SAN MIGUEL OESTE','department_id'=>13],

            ['code'=>'27','name'=>'MORAZAN NORTE','department_id'=>14],
            ['code'=>'28','name'=>'MORAZAN SUR','department_id'=>14],

            ['code'=>'19','name'=>'LA UNION NORTE','department_id'=>15],
            ['code'=>'20','name'=>'LA UNION SUR','department_id'=>15],
        ];

        foreach ($data as $item) {
            Municipality::create($item);
        }
    }
}