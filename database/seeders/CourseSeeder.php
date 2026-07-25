<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Course;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Course::create([
            'name' => 'Laravel',
            'description' => 'Description 1',
            'image' => 'https://www.flaticon.com/free-icons/php',
            'price' => '100',
        ]);
        Course::create([
            'name' => 'Vue Js',
            'description' => 'Description 2',
            'image' => 'https://www.flaticon.com/free-icons/vuejs',
            'price' => '200',
        ]);
        Course::create([
            'name' => 'Inertia JS',
            'description' => 'Description 3',
            'image' => 'https://www.flaticon.com/free-icons/react',
            'price' => '300',
        ]);
        Course::create([
            'name' => 'Tailwind CSS',
            'description' => 'Description 4',
            'image' => 'https://www.flaticon.com/free-icons/tailwind-css',
            'price' => '400',
        ]);
    }
}
