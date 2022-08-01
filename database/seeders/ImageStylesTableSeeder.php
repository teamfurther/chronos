<?php

namespace Chronos\Database\Seeders;

use Chronos\Models\ImageStyle;
use Illuminate\Database\Seeder;

class ImageStylesTableSeeder extends Seeder
{
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run()
    {
        ImageStyle::create([
            'name' => 'Chronos Thumbnail',
            'crop_height' => 200,
            'crop_width' => 200,
            'crop_type' => 'fit',
            'cloak' => 1
        ]);
    }
}
