<?php

namespace Chronos\Database\Seeders;

use Chronos\Models\Role;
use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run()
    {
        Role::create([
            'name' => 'root',
            'cloak' => 1
        ]);
    }
}
