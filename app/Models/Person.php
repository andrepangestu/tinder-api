<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Person extends Model
{
    use HasFactory;

    protected $table = 'people';

    protected $fillable = [
        'name',
        'age',
        'location',
        'image_url',
    ];

    protected $casts = [
    ];

    protected $appends = [
        'likes_count',
        'dislikes_count',
    ];

    /**
     * Get all activities for this person
     */
    public function activities(): HasMany
    {
        return $this->hasMany(PersonActivity::class);
    }

    /**
     * Get the likes count attribute
     */
    protected function likesCount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->activities()->where('action_type', 'like')->count(),
        );
    }

    /**
     * Get the dislikes count attribute
     */
    protected function dislikesCount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->activities()->where('action_type', 'dislike')->count(),
        );
    }

    /**
     * Scope to get recommended people
     */
    public function scopeRecommended($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}