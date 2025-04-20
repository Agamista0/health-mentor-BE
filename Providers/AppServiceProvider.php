<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
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
        // Add a helper function for blade views to get media URLs as relative paths
        Blade::directive('mediaUrl', function ($expression) {
            // Parse the expression to get variable name and collection name
            $parts = explode(',', $expression);
            $variable = trim($parts[0]);
            $collection = isset($parts[1]) ? trim($parts[1]) : "'images'";
            
            return "<?php echo \${$variable}->getRelativeMediaUrl({$collection}); ?>";
        });
    }
}
