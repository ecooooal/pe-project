<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // Don't forget to import DB
use Faker\Factory as Faker; // Import Faker

class ReviewersTable extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Loop to create multiple reviewer records
        foreach (range(1, 10) as $index) { // This will create 10 reviewer records
            DB::table('reviewers')->insert([
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'affiliation' => $faker->company, // Example: 'affiliation' column
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}