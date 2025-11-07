<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    use HasFactory;

    protected $table = 'people';

    protected $fillable = [
        'name',
        'age',
        'location',
        'bio',
        'image_url',
        'interests',
        'likes_count',
        'dislikes_count'
    ];

    protected $casts = [
        'interests' => 'array',
        'likes_count' => 'integer',
        'dislikes_count' => 'integer',
    ];

    /**
     * Scope to get recommended people
     */
    public function scopeRecommended($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Increment the likes count for this person
     */
    public function incrementLikes(): void
    {
        $this->increment('likes_count');
    }

    /**
     * Increment the dislikes count for this person
     */
    public function incrementDislikes(): void
    {
        $this->increment('dislikes_count');
    }
}