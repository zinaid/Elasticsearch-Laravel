<?php

namespace App\Articles;
use App\Models\Paper;
use Illuminate\Database\Eloquent\Collection;

class EloquentSearch implements SearchRepository
{
    public function search(string $term): Collection
    {
        return Paper::query()
            ->where(fn ($query) => (
                $query->where('content', 'LIKE', "%{$term}%")
                    ->orWhere('title', 'LIKE', "%{$term}%")
            ))
            ->get();
    }
} 