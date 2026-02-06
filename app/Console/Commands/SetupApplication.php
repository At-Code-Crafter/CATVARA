<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SetupApplication extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:setup-application';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup application with initial company and users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Application Setup...');

        // 1. Company Setup
        $company = \App\Models\Company\Company::first();
        if (! $company) {
            $this->info('No company found. Let\'s create your first company.');

            $name = $this->ask('Enter Company Name');
            $legalName = $this->ask('Enter Legal Name', $name);
            $code = $this->ask('Enter Company Code (e.g., CATVARA)', strtoupper(\Illuminate\Support\Str::slug($name)));

            // Ensure ACTIVE status exists
            $status = \App\Models\Company\CompanyStatus::firstOrCreate(
                ['code' => 'ACTIVE'],
                ['name' => 'Active', 'is_active' => true]
            );

            $company = \App\Models\Company\Company::create([
                'name' => $name,
                'legal_name' => $legalName,
                'code' => $code,
                'company_status_id' => $status->id,
            ]);

            $this->info("Company '{$name}' created successfully.");

            // Create default admin role for this company
            \App\Models\Auth\Role::firstOrCreate(
                ['company_id' => $company->id, 'slug' => 'admin'],
                ['name' => 'Admin', 'is_active' => true]
            );
        } else {
            $this->info("Using existing company: {$company->name}");
        }

        // 1.5 Create Default Super Admin
        $this->info('Checking for default Super Admin...');
        $adminEmail = 'admin@catvara.store';
        $adminPassword = 'Admin@12345';

        $superAdmin = \App\Models\User::where('email', $adminEmail)->first();
        if (! $superAdmin) {
            $superAdmin = \App\Models\User::create([
                'name' => 'Super Admin',
                'email' => $adminEmail,
                'email_verified_at' => now(),
                'password' => \Illuminate\Support\Facades\Hash::make($adminPassword),
                'password_changed_at' => now()->subYears(1), // Forced expiry
                'is_active' => true,
                'user_type' => 'SUPER_ADMIN',
            ]);
            $this->info("Default Super Admin created: {$adminEmail}");
        }

        // Link Super Admin to ALL companies
        $allCompanies = \App\Models\Company\Company::all();
        foreach ($allCompanies as $comp) {
            // Link to Company
            $superAdmin->assignToCompany($comp->id, true, true);

            // Ensure Admin role exists for this company
            $role = \App\Models\Auth\Role::firstOrCreate(
                ['company_id' => $comp->id, 'slug' => 'admin'],
                ['name' => 'Admin', 'is_active' => true]
            );

            // Link to Role
            $superAdmin->assignRole($role->id, $comp->id);
        }
        $this->info('Super Admin access ensured for all ('.$allCompanies->count().') companies.');

        // 2. User Creation Loop
        if ($this->confirm('Do you want to add additional users?', false)) {
            do {
                $this->info('Creating a new user...');
                $name = $this->ask('Enter User Name');
                $email = $this->ask('Enter Email');
                $password = $this->secret('Enter Password');

                $userType = $this->choice('Select User Type', ['SUPER_ADMIN', 'ADMIN', 'STAFF'], 'ADMIN');

                $companies = \App\Models\Company\Company::all();
                $companyNameArray = $companies->pluck('name')->toArray();

                if (count($companyNameArray) > 1) {
                    $selectedCompanyName = $this->choice('Select Company Access', $companyNameArray, $company->name);
                    $selectedCompany = $companies->where('name', $selectedCompanyName)->first();
                } else {
                    $selectedCompany = $company;
                }

                $roles = \App\Models\Auth\Role::where('company_id', $selectedCompany->id)->get();
                if ($roles->isEmpty()) {
                    $this->warn("No roles found for {$selectedCompany->name}. Creating 'admin' role.");
                    $role = \App\Models\Auth\Role::create([
                        'company_id' => $selectedCompany->id,
                        'slug' => 'admin',
                        'name' => 'Admin',
                        'is_active' => true,
                    ]);
                } else {
                    $roleName = $this->choice('Select Role for that Company', $roles->pluck('name')->toArray(), 'Admin');
                    $role = $roles->where('name', $roleName)->first();
                }

                // Create User
                $user = \App\Models\User::updateOrCreate(
                    ['email' => $email],
                    [
                        'name' => $name,
                        'email_verified_at' => now(),
                        'password' => \Illuminate\Support\Facades\Hash::make($password),
                        'is_active' => true,
                        'user_type' => $userType,
                    ]
                );

                // Link to Company
                $user->assignToCompany($selectedCompany->id, ($userType === 'SUPER_ADMIN'), true);

                // Link to Role
                $user->assignRole($role->id, $selectedCompany->id);

                $this->info("User '{$name}' created successfully and assigned to '{$selectedCompany->name}' as '{$role->name}'.");

            } while ($this->confirm('Do you want to add another user?', true));
        }

        $this->info('Application setup complete!');

        return 0;
    }
}
