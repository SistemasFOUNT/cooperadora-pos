<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ConfiguracionOrganizacion;

class ConfiguracionOrganizacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ConfiguracionOrganizacion::updateOrCreate(
            ['id' => 1],
            [
                'razon_social' => 'Asociación Cooperadora',
                'denominacion_comercial' => 'Facultad de Odontología - UNT',
                'cuit' => '30-12345678-9',
                'numero_ingresos_brutos' => '123456789',
                'domicilio_calle' => 'Av. Independencia',
                'domicilio_numero' => '1234',
                'domicilio_piso' => null,
                'domicilio_depto' => null,
                'localidad' => 'San Miguel de Tucumán',
                'codigo_postal' => '4000',
                'provincia' => 'Tucumán',
                'responsable_inscripto' => true,
                'retiene_ingresos_brutos' => true,
                'porcentaje_retencion_iibb' => 2.50,
                'categoria_iva' => 'responsable_inscripto',
                'telefono' => '0381-4247000',
                'email' => 'cooperadora@odont.unt.edu.ar',
                'sitio_web' => 'https://www.odont.unt.edu.ar',
                'logo_path' => 'images/system/logo-cooperadora.png',
                'pie_documentos' => 'El presente documento constituye título ejecutivo conforme a las disposiciones del Código Procesal Civil y Comercial. Para cualquier controversia será competente la Justicia de la Provincia de Tucumán.'
            ]
        );
    }
}
