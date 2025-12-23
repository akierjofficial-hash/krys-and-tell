<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

User::create([
    'name' => 'Admin',
    'email' => 'admin@clinic.com',
    'password' => Hash::make('admin123'),
    'role' => 'admin',
]);

User::create([
    'name' => 'Staff',
    'email' => 'staff@clinic.com',
    'password' => Hash::make('staff123'),
    'role' => 'staff',
]);
