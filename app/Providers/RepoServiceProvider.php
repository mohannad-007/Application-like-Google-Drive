<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepoServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->app->bind('App\Repository\UserRepositoryInterface', 'App\Repository\UserRepository');
        $this->app->bind('App\Repository\GroupRepositoryInterface', 'App\Repository\GroupRepository');
        $this->app->bind('App\Repository\FileRepositoryInterface', 'App\Repository\FileRepository');


    }


    public function boot()
    {
        //
    }
}
