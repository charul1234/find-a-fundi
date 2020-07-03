<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

use App\User;
use App\ExperienceLevel;
use App\PaymentOption;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $superAdminRole = Role::updateOrCreate(['name' => 'admin']);
        $seekerRole = Role::updateOrCreate(['name' => 'seeker']);
        $providerRole = Role::updateOrCreate(['name' => 'provider']);

        $user = User::updateOrCreate(['email'=>'admin@example.com'],['name'=>'Super Admin',
                                                					 'email'=>'admin@example.com',
                                                                     'email_verified_at' => now(),
                                                					 'password'=>Hash::make('password')]);

        $user->assignRole($superAdminRole);


        ExperienceLevel::updateOrCreate(['title' => '0-2 years - novice/beginner']);
        ExperienceLevel::updateOrCreate(['title' => '3-5 years - intermediate/proficient']);
        ExperienceLevel::updateOrCreate(['title' => '5 years and above - advanced/expert']);
        PaymentOption::updateOrCreate(['title' => 'Completion of project']);
        PaymentOption::updateOrCreate(['title' => 'Weekly – last day week (Sunday)']);
        PaymentOption::updateOrCreate(['title' => 'Monthly – 1st of every month (next month)']);
    }
}