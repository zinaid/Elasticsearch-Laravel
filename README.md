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


