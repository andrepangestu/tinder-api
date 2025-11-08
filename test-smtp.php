<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Mail;

echo "\n📧 Testing SMTP Connection to Mailtrap...\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Mail Configuration:\n";
echo "  Mailer: " . config('mail.default') . "\n";
echo "  Host: " . config('mail.mailers.smtp.host') . "\n";
echo "  Port: " . config('mail.mailers.smtp.port') . "\n";
echo "  Username: " . config('mail.mailers.smtp.username') . "\n";
echo "  From: " . config('mail.from.address') . "\n\n";

try {
    echo "Sending test email...\n";
    
    Mail::raw('This is a test email from Tinder API.', function ($message) {
        $message->to('test@example.com')
                ->subject('Test Email - Tinder API');
    });
    
    echo "\n✅ Email sent successfully!\n";
    echo "📬 Check your Mailtrap inbox: https://mailtrap.io/inboxes\n\n";
    
} catch (\Exception $e) {
    echo "\n❌ Error sending email:\n";
    echo "  " . $e->getMessage() . "\n\n";
}
