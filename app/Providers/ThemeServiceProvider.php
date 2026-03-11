<?php

namespace App\Providers;

use App\Console\Commands\ImportBlog;
use Roots\Acorn\Sage\SageServiceProvider;
use AppCore\Domain\Model\ModelRepository;
use AppCore\Infrastructure\Persistence\WPModelRepository;

class ThemeServiceProvider extends SageServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        parent::register();
        $this->app->bind(ModelRepository::class, WPModelRepository::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        if ($this->app->runningInConsole()) {
            $this->commands([
                ImportBlog::class,
            ]);
        }
    }
}
