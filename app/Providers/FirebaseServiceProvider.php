<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Google\Client as GoogleClient;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Contract\Storage;

class FirebaseServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Database::class, function ($app) {
            $factory = (new Factory)
                ->withServiceAccount(config('firebase.credentials.file'))
                ->withDatabaseUri(config('firebase.database.url'));

            return $factory->createDatabase();
        });

        $this->app->singleton(Storage::class, function ($app) {
            $factory = (new Factory)
                ->withServiceAccount(config('firebase.credentials.file'));

            return $factory->createStorage();
        });

        $this->app->singleton(GoogleClient::class, function ($app) {
            $client = new GoogleClient();
            $client->setAuthConfig(config('firebase.credentials.file'));
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            return $client;
        });
    }
} 