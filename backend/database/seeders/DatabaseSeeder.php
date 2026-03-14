<?php
namespace Database\Seeders;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email'=>'admin@aliensstore.com'],
            ['name'=>'Administrador','password'=>Hash::make('Admin@123')]
        );
    }
}
