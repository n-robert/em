<?php

namespace App\Providers;

use App\Http\Routing\EMRedirector;
use App\Http\Routing\EMUrlGenerator;
use Illuminate\Routing\Redirector;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->extend('url', function (UrlGenerator $urlGenerator) {
            return new EMUrlGenerator(
                $this->app->make('router')->getRoutes(),
                $urlGenerator->getRequest(),
                $this->app->make('config')->get('app.asset_url')
            );
        });
        $this->app->extend('redirect', function (Redirector $redirector) {
            return new EMRedirector($redirector->getUrlGenerator());
        });

        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        } else {
            URL::forceScheme('https');
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Inertia::share(
            [
                'errors' => function () {
                    return
                        Session::get('errors') ?
                            Session::get('errors')->getBag('default')->getMessages() : (object)[];
                },

                'canEdit' => function () {
                    return Gate::allows('can-edit');
                },

                'locale' => function () {
                    return app()->getLocale();
                },

                'language' => function () {
                    return get_translations();
                },

                '_token' => function () {
                    return csrf_token();
                },

                'views' => function () {
                    return session('views');
                },

                'defaultDateFormat' => function () {
                    return 'dd-MM-yyyy';
                },

                'currentRouteName' => function () {
                    return Route::currentRouteName();
                }
            ]
        );
    }
}
