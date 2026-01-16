<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UpdatePasswordHintsSeeder extends Seeder
{
    public function run(): void
    {
        // Update password_hint for existing users
        // This sets a default password for users created before password_hint was added

        $users = User::whereNull('password_hint')->get();

        foreach ($users as $user) {
            // Set default password based on role or email
            if (str_contains($user->email, 'admin')) {
                $user->password_hint = 'password';
            } elseif (str_contains($user->email, 'cashier')) {
                $user->password_hint = 'password';
            } else {
                $user->password_hint = 'password';
            }
            $user->save();
            echo "Updated password_hint for: {$user->email}\n";
        }

        echo "Total users updated: {$users->count()}\n";
    }
}
