<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $dispatchesEvents = [
      'created' => PostObserver::class,
    ];

    protected $fillable = [
        'user_id', 'title', 'description'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }


}
