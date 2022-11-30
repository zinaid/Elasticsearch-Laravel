<?php

namespace App\Papers;

use Illuminate\Database\Eloquent\Collection;

interface PapersRepository
{
    public function search(string $query = ''): Collection;
}