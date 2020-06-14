<?php

namespace App\Services\Generators;

use App\Helpers\Helper;
use App\Models\Entities\EManufacturer;

class ManufacturersGenerator extends AbstractGenerator
{
    /**
     * @return bool
     */
    public function run()
    {
        $manufacturers = [
            'Acerbis',
            'AFX North America Inc.',
            'AGV',
            'AGV Sport',
            'Airoh',
            'Alpinestars',
            'Answer Racing',
            'A-Pro',
            'Arai',
            'Arlen Ness',
            'AXO Sports',
            'Bell helmets',
            'Belstaff',
            'Berik Design',
            'BMW Motorrad',
            'Caberg S.r.l',
            'Cromwell',
            'Dainese S.p.A.',
            'Daytona',
            'Daytona Helmets',
            'Diadora',
            'Draggin Jeans',
            'Ducati',
            'Duraleu Helmets',
            'Ed Hardy Helmets',
            'EVS Sports',
            'Gianni Falco Motorcycle Boots',
            'Five',
            'Forma',
            'Fox Head Inc.',
            'Fulmer',
            'Gaerne S.p.a',
            'Gimoto',
            'Givi',
            'Grex',
            'Halvarssons',
            'Harley Davidson Motorclothes Merchandise',
            'Hein Gericke Gmbh',
            'Held GmbH',
            'HJC Helmets',
            'Icon Motorsport',
            'IDI Helmets',
            'IXON',
            'IXS Motorcycle Fashion',
            'Joe Rocket',
            'Jofama',
            'Karl Kochmann Gmbh',
            'KBC Helmets',
            'PlanetKnox Ltd.',
            'Kushitani',
            'Lazer Helmets',
            'Lindstrands',
            'Lookwell',
            'Madif Industries Ltd.',
            'Marushin Helmets',
            'MT Helmets',
            'MTech',
            'MVD Racewear',
            'New Max',
            'NEXX',
            'Nolan',
            'NZI Helmets',
            'OJ Atmosfere Metropolitane',
            'Onbrain',
            'O\'Neal',
            'OSBE Helmets',
            'Oxtar',
            'Progrip',
            'Racer',
            'Rev\'IT',
            'RS Taichi Inc.',
            'Rukka',
            'Schuberth',
            'ScorpionExo',
            'Scott sports',
            'Shark Helmets',
            'Shift Racing',
            'Shiro Helmets',
            'Shoei',
            'SIDI',
            'Sinisalo Sport Ltd.',
            'SixSixOne',
            'Spidi',
            'Spyke',
            'Suomy S.p.a.',
            'TCX',
            'Teknic',
            'Thor MX',
            'Troy Lee Designs',
            'UFO Plast',
            'Ultimate Stuff',
            'UVEX SPORTS GmbH',
            'Vemar S.r.l.',
        ];

        foreach ($manufacturers as $manufacturer) {
            $table = EManufacturer::table();

            $slug = Helper::translit($manufacturer);
            $query = "INSERT INTO {$table}
                (name, slug, visible)
            VALUES ('$manufacturer', '$slug', 1);";
            $this->db->exec($query);
        }
    }
}