<?php

namespace Database\Seeders;

use App\Models\CareerFeeConfig;
use Illuminate\Database\Seeder;

class CareerFeeConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $careers = [
            [
                'career_type' => 'tecnicatura_protesis',
                'career_name' => 'Tecnicatura en Prótesis Dental',
                'monthly_fee' => 25000.00,
                'enrollment_fee' => 15000.00,
                'certificate_fee' => 8000.00,
                'duration_months' => 24,
                'additional_fees' => [
                    'laboratorio' => 5000.00,
                    'materiales' => 3000.00
                ]
            ],
            [
                'career_type' => 'tecnicatura_asistencia',
                'career_name' => 'Tecnicatura en Asistencia Odontológica',
                'monthly_fee' => 22000.00,
                'enrollment_fee' => 12000.00,
                'certificate_fee' => 7000.00,
                'duration_months' => 24,
                'additional_fees' => [
                    'practica_clinica' => 4000.00
                ]
            ],
            [
                'career_type' => 'grado_odontologia',
                'career_name' => 'Grado en Odontología',
                'monthly_fee' => 45000.00,
                'enrollment_fee' => 30000.00,
                'certificate_fee' => 15000.00,
                'duration_months' => 60,
                'additional_fees' => [
                    'laboratorio' => 8000.00,
                    'materiales' => 6000.00,
                    'practica_clinica' => 10000.00
                ]
            ],
            [
                'career_type' => 'postgrado',
                'career_name' => 'Postgrado - Especialización',
                'monthly_fee' => 35000.00,
                'enrollment_fee' => 20000.00,
                'certificate_fee' => 12000.00,
                'duration_months' => 24,
                'additional_fees' => [
                    'seminarios' => 5000.00,
                    'tesis' => 8000.00
                ]
            ],
        ];

        foreach ($careers as $career) {
            CareerFeeConfig::create($career);
        }
    }
}
