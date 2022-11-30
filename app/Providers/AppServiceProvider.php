<?php

namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use App\Papers\EloquentSearch;
use App\Papers\PapersRepository;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            PapersRepository::class, EloquentSearch::class);
    }

    
    public function boot()
    {
        //
    }
}
