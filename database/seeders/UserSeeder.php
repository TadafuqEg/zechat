<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Section;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Faker\Factory as Faker;
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Section::create([
            'name' => 'Section 1',
        ]);
        $guards = ['admin','user','super admin'];
        for($i=0;$i<2;$i++){
            $role=Role::where('name',$guards[$i])->first();
            $user=User::create([
                'name' => $guards[$i],
                'email' => $guards[$i].'@gmail.com',
                'password' => Hash::make('123456'),
                'guard' => $guards[$i],
            ]);
        }
        // $faker = Faker::create();
        
        // for($i=0;$i<200;$i++){
        //     User::create([
        //         'name' => $faker->name,
        //         'email' => $faker->email,
        //         'password' => Hash::make('123123'),
        //     ]);
        // }
    }
}
