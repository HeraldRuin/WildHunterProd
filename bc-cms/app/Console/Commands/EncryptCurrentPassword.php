<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;

class EncryptCurrentPassword extends Command
{
    protected $signature = 'user:encrypt-password {user_id}';
    protected $description = 'Encrypt current_password for a user';

    public function handle()
    {
        $userId = $this->argument('user_id');
        $user = User::find($userId);

        if (!$user) {
            $this->error("User {$userId} not found.");
            return 1;
        }

        if ($user->current_password === null) {
            $this->warn("User {$userId} has null current_password. Skipping.");
            return 0;
        }

        $user->current_password = Crypt::encryptString($user->current_password);
        $user->save();

        $this->info("User {$userId} current_password encrypted successfully!");
        return 0;
    }
}
