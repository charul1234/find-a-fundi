<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

use App\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $superAdminRole = Role::updateOrcreate(['name' => 'admin']);
        $seekerRole = Role::updateOrcreate(['name' => 'seeker']);
        $providerRole = Role::updateOrcreate(['name' => 'provider']);

        $user = User::updateOrcreate(['email'=>'admin@example.com'],['name'=>'Super Admin',
                                                					 'email'=>'admin@example.com',
                                                                     'email_verified_at' => now(),
                                                					 'password'=>Hash::make('password')]);

        $user->assignRole($superAdminRole);

        // // For create dummy users
        // $users = factory(User::class, 3)->create();

        // $users->each(function($user) use ($userRole){
        //     $user->assignRole($userRole);
        // });
    }
}
