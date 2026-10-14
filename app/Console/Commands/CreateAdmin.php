<?php

namespace App\Console\Commands;

use App\Models\Profile;
use App\Models\User;
use App\Services\SlugService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateAdmin extends Command
{
    protected $signature = 'app:create-admin';

    protected $description = 'Securely create the initial super administrator';

    public function handle(): int
    {
        $name = trim((string) $this->ask('Full name'));
        $email = trim((string) $this->ask('Email'));
        $password = (string) $this->secret('Password');
        $parts = preg_split('/\s+/', $name, 2);
        $data = [
            'first_name' => $parts[0] ?? 'Admin',
            'last_name' => $parts[1] ?? 'User',
            'email' => $email,
            'password' => $password,
        ];

        $validator = Validator::make($data, [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', Password::min(12)->letters()->mixedCase()->numbers()->symbols()],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        DB::transaction(function () use ($data): void {
            // The User model's `hashed` cast hashes the plain password exactly once.
            $user = User::create($data);
            $user->forceFill([
                'role' => 'super_administrator',
                'email_verified_at' => now(),
            ])->save();

            Profile::create([
                'user_id' => $user->id,
                'slug' => SlugService::unique(Profile::class, $user->name),
                'status' => 'approved',
                'is_public' => false,
                'approved_at' => now(),
            ]);
        });

        $this->info('Initial super administrator created successfully.');

        return self::SUCCESS;
    }
}
