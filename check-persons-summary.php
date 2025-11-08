<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Person;

echo "\n📊 Summary of All Persons:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$persons = Person::all();

foreach ($persons as $person) {
    $likes = $person->likes_count;
    $status = $likes > 50 ? '🔥 POPULAR' : '   Regular';
    
    printf(
        "%s | ID: %3d | %-25s | Likes: %3d\n",
        $status,
        $person->id,
        $person->name,
        $likes
    );
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$popular = $persons->filter(fn($p) => $p->likes_count > 50);

echo "\n✅ Total Persons: " . $persons->count() . "\n";
echo "🔥 Popular Persons (>50 likes): " . $popular->count() . "\n";
echo "\n💌 Email would be sent to admin for the " . $popular->count() . " popular person(s)\n\n";
