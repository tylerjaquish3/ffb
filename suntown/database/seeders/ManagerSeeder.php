<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class ManagerSeeder extends Seeder
{
    /**
     * The 10 Suntown managers, in the league's traditional order.
     * Tyler is the commissioner.
     */
    const MANAGERS = [
        'Tyler', 'AJ', 'Gavin', 'Matt', 'Cameron',
        'Andy', 'Everett', 'Justin', 'Cole', 'Ben',
    ];

    const DEFAULT_PASSWORD = 'suntown2026';

    public function run(): void
    {
        foreach (self::MANAGERS as $name) {
            $user = User::firstOrCreate(
                ['email' => strtolower($name).'@suntownffb.local'],
                [
                    'name' => $name,
                    'password' => self::DEFAULT_PASSWORD,
                    'is_commissioner' => $name === 'Tyler',
                    'email_verified_at' => now(),
                ]
            );

            Team::firstOrCreate(
                ['user_id' => $user->id],
                ['name' => "{$name}'s Team"]
            );
        }
    }
}
