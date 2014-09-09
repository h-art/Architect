<?php namespace Hart\Architect;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\App;

class ArchitectServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        // register the package
        $this->package('hart/architect');

        // get stuff from configuration
        $admin_classes_namespace = Config::get('architect::admin_classes_namespace');
        $admin_classes = Config::get('architect::admin_classes');

        foreach ($admin_classes as $admin_class) {
            $class = $admin_classes_namespace . $admin_class;
            App::singleton($admin_class, $class); // register class in IoC container

            // register routes
            App::make($admin_class)->registerRoutes();
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array();
    }
}
