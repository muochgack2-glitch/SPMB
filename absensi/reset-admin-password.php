<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::where('email', 'admin@example.com')->first();
$user->password = bcrypt('password');
$user->save();

echo "✅ Password updated successfully!\n";
echo "Email: {$user->email}\n";
echo "Password: password\n";
