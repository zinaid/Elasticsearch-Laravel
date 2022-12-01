<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Search\Searchable;

class Paper extends Model
{
    use HasFactory;
    use Searchable;

    protected $casts = [
        'keywords' => 'json',
    ];
}
