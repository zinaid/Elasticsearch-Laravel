<?php

namespace App\Papers;

use App\Paper;
use App\Papers\PapersRepository;
use Illuminate\Database\Eloquent\Collection;

class EloquentSearch implements PapersRepository
{
    public function search(string $query = ''): Collection
    {
        return Paper::query()
            ->where('body', 'like', "%{$query}%")
            ->orWhere('title', 'like', "%{$query}%")
            ->get();
    }
}