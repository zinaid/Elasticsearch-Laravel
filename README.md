# ELASTICSEARCH WITH LARAVEL

In this project we will try to integrate Laravel with Elasticsearch. We will use Docker for creating microservises.

First of all create a clean Laravel project with command:

```composer create-project --prefer-dist laravel/laravel elasticsearch-test```

After that we will push Laravel Sail docker-compose.yml file.

```php artisan sail:install --with=mysql```

Docker with its services is started with:

```./vendor/bin/sail up -d```

If everything works fine you can open your application on the link:

```localhost:8000```, as well as the mysql on the link ```localhost:3006```.

Then we will add elasticsearch to our services in docker-compose.yml file using these lines of code:

```
elasticsearch:
         image: docker.elastic.co/elasticsearch/elasticsearch:7.5.2
         environment:
             - discovery.type=single-node
         ports:
             - 9200:9200
             - 9300:9300
         volumes:
             - sailelasticsearch:/usr/share/elasticsearch/data
         networks:
             - sail 
```

Abovementioned code creates a container elasticsearch and downloads its image. Ports used by elasticsearch are 9200 and 9300. We also define a constant volume for elasticsearch data. We also say that elasticsearch belongs to our network. Besides that we also add volume named for eq. sailelasticsearch.

```
volumes:
     sailmysql:
         driver: local
     sailelasticsearch:
         driver: local 
```

Docker is started using the command:

```
./vendor/bin/sail up -d
```

With the above code we downloaded the official Elasticsearch image and started it as an image in Docker. We can check the work of our Elasticsearch service with curl on port 9200.

```
 curl localhost:9200
```

The response should be:

```
{
  "name" : "9f4511cabca0",
  "cluster_name" : "docker-cluster",
  "cluster_uuid" : "_MKrhY9rQ7yeu8WE-7Bi_g",
  "version" : {
    "number" : "7.5.2",
    "build_flavor" : "default",
    "build_type" : "docker",
    "build_hash" : "8bec50e1e0ad29dad5653712cf3bb580cd1afcdf",
    "build_date" : "2020-01-15T12:11:52.313576Z",
    "build_snapshot" : false,
    "lucene_version" : "8.3.0",
    "minimum_wire_compatibility_version" : "6.8.0",
    "minimum_index_compatibility_version" : "6.0.0-beta1"
  },
  "tagline" : "You Know, for Search"
}
```
If we get this kind of response we are set to go, because we have all of our services set and running.

## APP

For this example we will create an application that contains a table of scientific papers through which we want to search.

First of all we will install elasticsearch to our application using composer.

```
./vendor/bin/sail composer require elasticsearch/elasticsearch
```

Because we don't want to lose time on frontend development and auth creation, we will use Laravel Breeze package and scaffold a basic application.

```
./vendor/bin/sail composer require laravel/breeze --dev
```

After that we can install breeze, pull all npm dependecies, compile assets and run migration because we need our User model and table for breeze to work.

```
./vendor/bin/sail artisan breeze:install
```

Npm dependecies and assets compilation should be done out of the wsl.

```
npm install
npm run dev
```

Then we need to migrate our tables to mysql database.
```
./vendor/bin/sail artisan migrate
```
Now our application has a login and registar functionalities with elementary tailwind dashboard, and auth frontend set.

After registering a new user and after we test our login functionalities, we approach to our main part and that is creating Paper model. Besides a model, we will create a migration and a factory (for seeding the database with dummy data).

```
./vendor/bin/sail artisan make:model -mf Paper
```
Our model Paper.php, migration and factory PaperFactory.php are created. Then we will modify our migration by adding some fields that we will use in our search.

```
public function up()
    {
        Schema::create('papers', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->json('keywords');
            $table->timestamps();
        });
    }
```

Keywords are going to be an Array, but they need to be stored as a JSON into a papers table. Because of that we need to modify our Paper.php model to cast it like so:

```
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paper extends Model
{
    use HasFactory;
    protected $casts = [
        'keywords' => 'json',
    ];
}
```
Then, we generate some dummy data using seeders and factory for our Paper model. Inside DatabaseSeeder.php that is placed under database/seeders we add the following code.

```
<?php

namespace Database\Seeders;
use App\Models\Paper;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Paper::factory()->times(50)->create();
    }
}
```

Then we modify created factory PaperFactory.php with the following code:

```
<?php
 namespace Database\Factories;
 use App\Models\Paper;
 use Illuminate\Database\Eloquent\Factories\Factory;
 class ArticleFactory extends Factory
 {
    protected $model = Paper::class;
    public function definition()
    {
        return [
            'title' => $this->faker->sentence(),
            'content' => $this->faker->text(),
            'keywords' => collect(['kafka', 'python', 'php', 'javascript', 'elasticsearch'])
                ->random(2)
                ->values()
                ->all(),
        ];
    }
} 
```

Now we test our seeders and migrate our 50 papers.

```
./vendor/bin/sail artisan migrate:fresh --seed

```

For viewing our entered papers we will modify dashboard.blade.php to render list of all papers.

```
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Papers') }} <span class="text-gray-400">({{ $papers->count() }})</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                @forelse ($papers as $paper)
                    <papers class="space-y-1">
                        <h2 class="font-semibold text-2xl">{{ $paper->title }}</h2>

                        <p class="m-0">{{ $paper->body }}</body>

                        <div>
                            @foreach ($paper->keywords as $keyword)
                                <span class="text-xs px-2 py-1 rounded bg-indigo-50 text-indigo-500">{{ $keyword}}</span>
                            @endforeach
                        </div>
                    </papers>
                @empty
                    <p>No papers found</p>
                @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

Ofcoure, we now need to modify ```dashboard``` route:
```
Route::get('/dashboard', function () {
    return view('dashboard', [
        'papers' => App\Models\Paper::all(),
    ]);
})->middleware(['auth'])->name('dashboard'); 
```

We will create two ways of fetching data, using Eloquent and using Elasticsearch. We will add repository (using repository pattern) Papers inside app. Then we will create repository interface:

```
<?php

namespace App\Papers;

use Illuminate\Database\Eloquent\Collection;

interface PapersRepository
{
    public function search(string $query = ''): Collection;
}
```

Then we will create our Eloquent search implementation inside the same folder Papers and inside EloquentSearch.php.

```
<?php

namespace App\Papers;

use App\Models\Paper;
use App\Papers\PapersRepository;
use Illuminate\Database\Eloquent\Collection;

class EloquentSearch implements PapersRepository
{
    public function search(string $query = ''): Collection
    {
        return Paper::query()
            ->where('content', 'like', "%{$query}%")
            ->orWhere('title', 'like', "%{$query}%")
            ->get();
    }
}
```` 

Now we bind that interface in our AppServiceProviders.php.

```
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
```
Then we need to modify our dashboard route in web.php because it needs to support searching for articles. It will accept `q` Query String and then it will use the repository we have created instead of listing papers.

```
<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Papers\PapersRepository;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function (PapersRepository $repository) {
    return view('dashboard', [
        'papers' => request()->has('q')
            ? $repository->search(request('q'))
            : App\Models\Paper::all(),
    ]);
})->middleware(['auth'])->name('dashboard'); 
```

After that we will add our search input inside dashboard.blade.html. This will be a regular form with method GET and action to dashboard. It will have one input with the name `q`.

```
<x-app-layout>
     <x-slot name="header">
         <h2 class="font-semibold text-xl text-gray-800 leading-tight">
             {{ __('Papers') }} <span class="text-gray-400">({{ $papers->count() }})</span>
         </h2>
     </x-slot>
     <div class="py-12">
         <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
             <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                 <div class="p-6 bg-white border-b border-gray-200">
                     <form action="{{ route('dashboard') }}" method="get" class="pb-4">
                         <div class="form-group">
                             <x-text-input
                                 type="text"
                                 name="q"
                                 class="form-control"
                                 placeholder="Search..."
                                 value="{{ request('q') }}"
                             />
                         </div>
                     </form>
                     @if (request()->has('q'))
                         <p class="text-sm">Using search: <strong>"{{ request('q') }}"</strong>. <a class="border-b border-indigo-800 text-indigo-800" href="{{ route('dashboard') }}">Clear filters</a></p>
                     @endif
                     <div class="mt-8 space-y-8">
                         @forelse ($papers as $paper)
                             <article class="space-y-1">
                                 <h2 class="font-semibold text-2xl">{{ $paper->title }}</h2>
                                 <p class="m-0">{{ $paper->body }}</body>
                                 <div>
                                     @foreach ($paper->keywords as $keyword)
                                         <span class="text-xs px-2 py-1 rounded bg-indigo-50 text-indigo-500">{{ $keyword}}</span>
                                     @endforeach
                                 </div>
                             </article>
                         @empty
                             <p>No papers found</p>
                         @endforelse
                     </div>
                 </div>
             </div>
         </div>
     </div>
 </x-app-layout> 
```

## ELASTICSEARCH VARIANT OF SEARCH

Elasticsearch uses REST Api to connect with its database. Now we need to connect our Eloquent models we want to index to it and send HTTP requests to Elasticsearch API. We are going to use Model Observers bacause it allows us the same setup for different model types we need to index.

Elasticsearch observer is created with command:
```
<?php

namespace App\Search;

use Elasticsearch\Client;

class ElasticsearchObserver
{
    public function __construct(private Client $elasticsearchClient)
    {
        // ...
    }

    public function saved($model)
    {
        $model->elasticSearchIndex($this->elasticsearchClient);
    }

    public function deleted($model)
    {
        $model->elasticSearchDelete($this->elasticsearchClient);
    }
}
```

Now we bind this observer in all of our Models that we want to index in Elasticsearch. We do that creating a new trait. This trait is going to be called Searchable trait. This trait will provide methods the observer uses.

```
<?php

namespace App\Search;

trait Searchable
{
    public static function bootSearchable()
    {
        // This makes it easy to toggle the search feature flag
        // on and off. This is going to prove useful later on
        // when deploy the new search engine to a live app.
        if (config('services.search.enabled')) {
            static::observe(ElasticSearchObserver::class);
        }
    }

    public function getSearchIndex()
    {
        return $this->getTable();
    }

    public function getSearchType()
    {
        if (property_exists($this, 'useSearchType')) {
            return $this->useSearchType;
        }

        return $this->getTable();
    }

    public function toSearchArray()
    {
        // By having a custom method that transforms the model
        // to a searchable array allows us to customize the
        // data that's going to be searchable per model.
        return $this->toArray();
    }
}
```

Now when we can use this trait in any model we want.

```
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

```

When we create, delete or update Paper using Eloquent we trigger ElasticsearchObserver that syncs the data. This proccess is synchronous HTTP request. We may upgrade this using some Queue messaging platform (that is a project upgrade for future).

We now can feed Elasticsearch with papers. Now we can create a new implementation of 
our SearchRepository interface, this time with elasticsearch methods.

```
<?php

namespace App\Papers;

use App\Paper;
use Elasticsearch\Client;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Collection;

class ElasticsearchRepository implements ArticlesRepository
{
    /** @var \Elasticsearch\Client */
    private $elasticsearch;

    public function __construct(Client $elasticsearch)
    {
        $this->elasticsearch = $elasticsearch;
    }

    public function search(string $query = ''): Collection
    {
        $items = $this->searchOnElasticsearch($query);

        return $this->buildCollection($items);
    }

    private function searchOnElasticsearch(string $query = ''): array
    {
        $model = new Paper;

        $items = $this->elasticsearch->search([
            'index' => $model->getSearchIndex(),
            'type' => $model->getSearchType(),
            'body' => [
                'query' => [
                    'multi_match' => [
                        'fields' => ['title^5', 'body', 'tags'],
                        'query' => $query,
                    ],
                ],
            ],
        ]);

        return $items;
    }

    private function buildCollection(array $items): Collection
    {
        $ids = Arr::pluck($items['hits']['hits'], '_id');

        return Paper::findMany($ids)
            ->sortBy(function ($article) use ($ids) {
                return array_search($article->getKey(), $ids);
            });
    }
}
```

We perform search using Elasticsearch, and then we do findMany SQL search using items provided by the search.

We can also switch between the repository by modifying binding in the ServiceProvider:

```
 public function register()
    {
        $this->app->bind(Articles\ArticlesRepository::class, function ($app) {
            // This is useful in case we want to turn-off our
            // search cluster or when deploying the search
            // to a live, running application at first.
            if (! config('services.search.enabled')) {
                return new Articles\EloquentRepository();
            }

            return new Articles\ElasticsearchRepository(
                $app->make(Client::class)
            );
        });
    }
```

Now, when we ask for PapersRepository interfaced object it will give an Elasticsearch instance if it is enabled, otherwise it will return Eloquent version. We do some customization for elasticsearch client and bind it in the AppServiceProvider.

```
<?php

namespace App\Providers;

use App\Papers;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(Papers\PapersRepository::class, function () {
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

        $this->bindSearchClient();
    }

    private function bindSearchClient()
    {
        $this->app->bind(Client::class, function ($app) {
            return ClientBuilder::create()
                ->setHosts($app['config']->get('services.search.hosts'))
                ->build();
        });
    }
}
```

Next we finish configuration. We change config/services.php.

```
'search' => [
    'enabled' => env('ELASTICSEARCH_ENABLED', false),
    'hosts' => explode(',', env('ELASTICSEARCH_HOSTS')),
],
```

After we set configuration we also define environment variables in .env file.

```
ELASTICSEARCH_ENABLED=true
ELASTICSEARCH_HOSTS="localhost:9200"
```

Next we need to populate Elasticsearch with our existing data. For that we will create a custom artisan command. This command can be used later if we change our schemes of our Elasticsearch indexes. 

We are exploding the hosts here to allow passing multiple hosts using a comma-separated list, but we are not using that at the moment. If you have your php server running, don’t forget to reload it so it fetches the new configs. After that, we need to populate Elasticsearch with our existing data.

We create CLI command:

```
./vendor/bin/sail php artisan make:command ReindexCommand --command="search:reindex"
```

And then we modify our ReindexCommand as:

```
```

This command is called with:
```
./vendor/bin/sail php artisan search:reindex
```