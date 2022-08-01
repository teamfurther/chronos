<?php

namespace Chronos\Database\Seeders;

use App\Models\User;
use Chronos\Models\Role;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run()
    {
        $user = User::create([
            'email' => 'root@gofurther.digital',
            'password' => bcrypt('eidg0awy'),
            'firstname' => 'Super',
            'lastname' => 'Admin'
        ]);
        $user->role()->associate(Role::where('name', 'root')->first());
        $user->save();
    }
}
