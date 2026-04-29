<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\Helpers\PermHelper;

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
        // ── @canperm('module', 'action') ... @endcanperm ──────────────────
        // Renders the block only if the current user has the permission.
        // Example: @canperm('leads', 'add') <button>Add Lead</button> @endcanperm
        Blade::directive('canperm', function ($expression) {
            return "<?php if (\\App\\Helpers\\PermHelper::can({$expression})): ?>";
        });

        Blade::directive('endcanperm', function () {
            return '<?php endif; ?>';
        });

        // ── @cannotperm('module', 'action') ... @endcannotperm ────────────
        Blade::directive('cannotperm', function ($expression) {
            return "<?php if (!\\App\\Helpers\\PermHelper::can({$expression})): ?>";
        });

        Blade::directive('endcannotperm', function () {
            return '<?php endif; ?>';
        });
    }
}
