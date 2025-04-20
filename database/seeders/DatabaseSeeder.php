<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Subscription;
use App\Models\SubscriptionBenefit;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // First create basic users and roles
        \App\Models\User::factory(10)->create();
        
        // Create specialities first
        \App\Models\Speciality::factory(20)->create();
        
       
        
        // Create questions and answers
        \App\Models\Question::factory(50)->create();
        \App\Models\SectionQuestion::factory(50)->create();
        \App\Models\Answer::factory(50)->create();
        
        // Create lifestyle categories
        $this->call(LifestyleCategorySeeder::class);

        // Create a test user
        \App\Models\User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
    }
}