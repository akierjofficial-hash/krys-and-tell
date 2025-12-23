<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;

class ServiceSeeder extends Seeder
{
    public function run()
    {
        $services = [
            ['name' => 'Braces', 'base_price' => 40000, 'allow_custom_price' => false],
            ['name' => 'Extraction', 'base_price' => 900, 'allow_custom_price' => true],
            ['name' => 'Filling', 'base_price' => 800, 'allow_custom_price' => true],
            ['name' => 'Pasta Front Teeth', 'base_price' => 1000, 'allow_custom_price' => false],
            ['name' => 'Cleaning/Oral Prophylaxis', 'base_price' => 800, 'allow_custom_price' => false],
            ['name' => 'Denture/Pustiso', 'base_price' => 3000, 'allow_custom_price' => true],
            ['name' => 'Retainers', 'base_price' => 5000, 'allow_custom_price' => false],
            ['name' => 'Teeth Whitening', 'base_price' => 6000, 'allow_custom_price' => false],
            ['name' => 'Consultation', 'base_price' => 300, 'allow_custom_price' => false],
            ['name' => 'Root Canal Therapy', 'base_price' => 8000, 'allow_custom_price' => true],
            ['name' => 'Impacted Oral Surgery', 'base_price' => 8000, 'allow_custom_price' => false],
            ['name' => 'Composite Veneers', 'base_price' => 2500, 'allow_custom_price' => false],
            ['name' => 'Indirect Veneers', 'base_price' => 8000, 'allow_custom_price' => false],
            ['name' => 'Checkup', 'base_price' => 300, 'allow_custom_price' => false],
            ['name' => 'X-Ray', 'base_price' => 600, 'allow_custom_price' => false],
            ['name' => 'LCF#55O', 'base_price' => 700, 'allow_custom_price' => false],
            ['name' => 'Severe Case', 'base_price' => 2000, 'allow_custom_price' => true],
            ['name' => 'Tooth Extraction #21,22', 'base_price' => 1800, 'allow_custom_price' => false],
            ['name' => 'Tooth Extraction #11,12', 'base_price' => 0, 'allow_custom_price' => false],
            ['name' => 'LCF #37,35,46,47', 'base_price' => 4000, 'allow_custom_price' => false],
        ];

        foreach ($services as $service) {
            Service::create($service);
        }
    }
}
