<?php

namespace Chronos\Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {

    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run()
    {
        $this->call(ImageStylesTableSeeder::class);
        $this->call(PermissionsTableSeeder::class);
        $this->call(RolesTableSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(GallerySeeder::class);
        $this->call(PermissionsTableSeeder::class);
    }

}