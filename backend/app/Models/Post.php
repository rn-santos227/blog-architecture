<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Post extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'user_id',
        'title',
        'body',
        'status',
        'published_at',
    ];

    protected function casts(): array {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo {
        return $this->belongsTo(user::class);
    }

    public function tags(): BelongsToMany {
        return $this->belongsToMany(Tag::class);
    }
}
