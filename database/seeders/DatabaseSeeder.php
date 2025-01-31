<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Add contact types
        DB::table('contact_types')->insert([
            ['type_name' => 'Email', 'description' => 'Email address'],
            ['type_name' => 'Phone', 'description' => 'Phone number'],
            ['type_name' => 'Friar', 'description' => 'Franciscan Friar'],
            ['type_name' => 'Nun', 'description' => 'Religious Sister'],
        ]);

        // Add a sample saint
        DB::table('saints')->insert([
            'name' => 'St. Francis of Assisi',
            'biography' => 'Founder of the Franciscan Order',
            'recurrence_date' => '2025-10-04',
            'feast_day' => '2025-10-04',
        ]);

        // Add a sample gospel
        DB::table('gospels')->insert([
            'gospel_verse' => 'John 3:16',
            'gospel_text' => 'For God so loved the world...',
            'evangelist' => 'John',
            'sacred_text_reference' => 'New Testament',
            'liturgical_period' => 'Ordinary Time',
        ]);
    }
}
