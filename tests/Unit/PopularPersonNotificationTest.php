<?php

namespace Tests\Unit;

use App\Mail\PopularPersonNotification;
use App\Models\Person;
use Illuminate\Support\Collection;
use Tests\TestCase;

class PopularPersonNotificationTest extends TestCase
{
    /**
     * Test that the mailable has the correct subject.
     */
    public function test_mailable_has_correct_subject(): void
    {
        $persons = new Collection([
            new Person(['id' => 1, 'name' => 'Test Person', 'age' => 25, 'location' => 'Jakarta']),
        ]);

        $mailable = new PopularPersonNotification($persons);
        $envelope = $mailable->envelope();

        $this->assertEquals('Alert: Popular Persons Detected (50+ Likes)', $envelope->subject);
    }

    /**
     * Test that the mailable uses the correct view.
     */
    public function test_mailable_uses_correct_view(): void
    {
        $persons = new Collection([
            new Person(['id' => 1, 'name' => 'Test Person', 'age' => 25, 'location' => 'Jakarta']),
        ]);

        $mailable = new PopularPersonNotification($persons);
        $content = $mailable->content();

        $this->assertEquals('emails.popular-person-notification', $content->view);
    }

    /**
     * Test that the mailable has no attachments.
     */
    public function test_mailable_has_no_attachments(): void
    {
        $persons = new Collection([
            new Person(['id' => 1, 'name' => 'Test Person', 'age' => 25, 'location' => 'Jakarta']),
        ]);

        $mailable = new PopularPersonNotification($persons);
        $attachments = $mailable->attachments();

        $this->assertIsArray($attachments);
        $this->assertEmpty($attachments);
    }

    /**
     * Test that the mailable stores the popular persons collection.
     */
    public function test_mailable_stores_popular_persons_collection(): void
    {
        $persons = new Collection([
            new Person(['id' => 1, 'name' => 'Person 1', 'age' => 25, 'location' => 'Jakarta']),
            new Person(['id' => 2, 'name' => 'Person 2', 'age' => 30, 'location' => 'Bandung']),
        ]);

        $mailable = new PopularPersonNotification($persons);

        $this->assertInstanceOf(Collection::class, $mailable->popularPersons);
        $this->assertCount(2, $mailable->popularPersons);
        $this->assertEquals('Person 1', $mailable->popularPersons->first()->name);
    }
}
