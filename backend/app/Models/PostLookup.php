<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostLookup extends Model
{
    protected $table = 'post_lookup';
    protected $fillable = [
        'post_id',
        'shard',
        'user_id',
    ];
}
