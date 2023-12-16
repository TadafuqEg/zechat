<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;
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
                'name' => $guards[$i],
                'email' => $guards[$i].'@gmail.com',
                'password' => Hash::make('123123'),
                'guard' => $guards[$i],
            ]);
        }
        $faker = Faker::create();
        
        for($i=0;$i<200;$i++){
            User::create([
                'name' => $faker->name,
                'email' => $faker->email,
                'password' => Hash::make('123123'),
            ]);
        }
    }
}
