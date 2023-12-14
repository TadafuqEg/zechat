<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $guards = ['admin','user'];
        for($i=0;$i<2;$i++){
            User::create([
                'name' => $guards[$i].'@gmail.com',
                'email' => $guards[$i].'@gmail.com',
                // 'phone' => '0120000000'.$i,
                'password' => Hash::make('123123'),
                // 'update_code' => rand('1000','9999'),
                'guard' => $guards[$i],
            ]);
        }
    }
}
