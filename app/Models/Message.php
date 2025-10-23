<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_id',
        'to_id',
        'body',
        'seen',
        'attachment',

    ];



    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_id');
    }


}

