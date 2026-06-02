<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\WorkDay;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class WorkDaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('12345678'), 
                'role' => 'admin',
            ]
        );
        $days = [
            'Sunday',
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
        ];

        foreach ($days as $day) {
            WorkDay::create([
                'day_name'   => $day,
                'start_time' => '08:00:00',
                'end_time'   => '16:00:00',
            ]);
        }
    }
}
