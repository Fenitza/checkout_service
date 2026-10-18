<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        Setting::firstOrCreate(
            ['key' => 'reward_every_n_orders'],
            ['value' => '5']
        );

        Setting::firstOrCreate(
            ['key' => 'reward_discount_percent'],
            ['value' => '10']
        );
    }
}
