<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\User::factory(10)->create();
        // \App\Models\Post::factory(10)->create();

        \App\Models\User::factory()->create([
            'okta_user_id' => 'p0126',
            'chinese_name' => '黃靖凱',
            'first_name' => 'Harvey',
            'last_name' => 'Huang',
            'email' => 'harvey.huang@staff.nueip.com',
        ]);
    }
}
