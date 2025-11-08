<?php

namespace App\Console\Commands;

use App\Mail\PopularPersonNotification;
use App\Models\Person;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CheckPopularPersons extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'persons:check-popular
                            {--threshold=50 : The minimum number of likes to be considered popular}
                            {--admin-email=admin@tinderapi.com : The admin email to send notifications to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for persons with more than 50 likes and notify admin';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $threshold = (int) $this->option('threshold');
        $adminEmail = $this->option('admin-email');

        $this->info("Checking for persons with more than {$threshold} likes...");

        // Get all persons with their activities
        $popularPersons = Person::all()->filter(function ($person) use ($threshold) {
            return $person->likes_count > $threshold;
        });

        if ($popularPersons->isEmpty()) {
            $this->info('No persons found with more than ' . $threshold . ' likes.');
            return Command::SUCCESS;
        }

        $this->info("Found {$popularPersons->count()} popular person(s).");
        
        // Display the popular persons
        $tableData = $popularPersons->map(function ($person) {
            return [
                'ID' => $person->id,
                'Name' => $person->name,
                'Age' => $person->age,
                'Location' => $person->location,
                'Likes' => $person->likes_count,
            ];
        })->toArray();

        $this->table(
            ['ID', 'Name', 'Age', 'Location', 'Likes'],
            $tableData
        );

        // Send email notification
        try {
            Mail::to($adminEmail)->send(new PopularPersonNotification($popularPersons));
            $this->info("Email notification sent to {$adminEmail}");
        } catch (\Exception $e) {
            $this->error("Failed to send email: " . $e->getMessage());
            return Command::FAILURE;
        }

        $this->info('Popular persons check completed successfully.');
        
        return Command::SUCCESS;
    }
}
