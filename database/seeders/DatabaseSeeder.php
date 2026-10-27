<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // فقط در محیط local یک ادمین دمو می‌سازد (در production هیچ کاربری ساخته نمی‌شود)
        $this->call(DemoUserSeeder::class);
    }
}
