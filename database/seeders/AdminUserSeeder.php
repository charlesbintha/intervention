<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $administratorEmails = collect(explode(',', (string) config('services.microsoft_sso.admin_emails')))
            ->map(fn (string $email): string => mb_strtolower(trim($email)))
            ->filter()
            ->unique();

        if ($administratorEmails->isEmpty()) {
            $this->command->warn('Aucun administrateur SSO configuré dans MICROSOFT_SSO_ADMIN_EMAILS.');

            return;
        }

        foreach ($administratorEmails as $email) {
            User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => Str::headline(Str::before($email, '@')),
                    'role' => 'admin',
                    'is_active' => true,
                ],
            );
        }

        $this->command->info('Les administrateurs Microsoft SSO ont été préparés.');
    }
}
