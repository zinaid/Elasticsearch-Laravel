<?php

namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use App\Papers\EloquentSearch;
use App\Papers\PapersRepository;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(Papers\PapersRepository::class, function ($app) {
            // This is useful in case we want to turn-off our
            // search cluster or when deploying the search
            // to a live, running application at first.
            if (! config('services.search.enabled')) {
                return new Papers\EloquentSearch();
            }

            return new Papers\Elasticsearch(
                $app->make(Client::class)
            );
        });
    }

    
    public function boot()
    {
        //
    }
}
