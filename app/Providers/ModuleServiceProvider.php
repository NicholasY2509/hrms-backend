<?php

namespace App\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $modulesPath = app_path('Modules');

        if (File::exists($modulesPath)) {
            $modules = File::directories($modulesPath);

            foreach ($modules as $module) {
                $this->registerModule($module);
            }
        }
    }

    /**
     * Register module components.
     *
     * @param string $modulePath
     */
    private function registerModule(string $modulePath): void
    {
        $moduleName = basename($modulePath);

        // Load Routes
        $routesPath = $modulePath . '/Routes';
        if (File::exists($routesPath)) {
            $routeFiles = File::files($routesPath);

            foreach ($routeFiles as $file) {
                if ($file->getExtension() === 'php' && $file->getFilenameWithoutExtension() !== 'web') {
                    $version = $file->getFilenameWithoutExtension(); // e.g., 'v1'
                    
                    Route::prefix("api/{$version}/" . strtolower($moduleName))
                        ->middleware('api')
                        ->group($file->getPathname());
                }
            }
        }

        if (File::exists($modulePath . '/Routes/web.php')) {
            Route::middleware('web')
                ->group($modulePath . '/Routes/web.php');
        }

        // Load Migrations
        if (File::exists($modulePath . '/Migrations')) {
            $this->loadMigrationsFrom($modulePath . '/Migrations');
        }

        // Load Views
        if (File::exists($modulePath . '/Views')) {
            $this->loadViewsFrom($modulePath . '/Views', $moduleName);
        }

        // Load Translations
        if (File::exists($modulePath . '/Translations')) {
            $this->loadTranslationsFrom($modulePath . '/Translations', $moduleName);
        }
    }
}
