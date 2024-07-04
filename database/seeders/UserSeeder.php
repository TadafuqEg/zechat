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
        // $section=Section::create([
        //     'name' => 'Section 1',
        // ]);
        $guard = 'super super admin';
       
            $role=Role::where('name',$guard)->first();
            $user=User::create([
                'name' => 'General Manager Admin',
                'email' => 'g.m.admin@gmail.com',
                'password' => Hash::make('gmadmin1594826!@#$0'),
                'guard' => $guard,
                'section_id'=>1,
                'is_online'=>0
            ]);
            $user->assignRole($role->id);
        
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
