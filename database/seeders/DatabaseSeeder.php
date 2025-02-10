<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Type;
use App\Models\Caliber;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();
        \App\Models\User::factory()->create([
            'name' => 'malek450',
            'password' => Hash::make('malek45088'),
            'type' => 1,
        ]);

        \App\Models\User::factory()->create([
            'name' => 'user',
            'password' => Hash::make('12341234')
        ]);

        Type::create([
            'name' => 'خاتم'
        ]);
        Caliber::create([
            'full_name' => 'عيار 21 ',
            'name' => 21
        ]);

    }
}
