<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AgentSeeder extends Seeder
{
    public function run()
    {
        $agents = [];

        for ($i = 1; $i <= 20; $i++) {
            $agents[] = [
                'name' => 'Agent ' . $i,
                'email' => 'agent' . $i . '@example.com',
                'phone' => '98765432' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'profile' => null,
                'company_name' => 'Company ' . $i,
                'address' => 'Street ' . $i,
                'city' => 'City ' . $i,
                'wallet' => rand(100, 1000),
                'state' => 'State ' . $i,
                'country' => 'Country ' . $i,
                'otp' => rand(100000, 999999),
                'expire_at' => Carbon::now()->addMinutes(10),
                'verification' => rand(0, 1),
                'status' => rand(0, 1),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('agents')->insert($agents);
    }
}
