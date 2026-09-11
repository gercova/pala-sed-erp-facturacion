<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\StockProduct;

class GlobalVariablesServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Compartir la variable en todas las vistas
        View::composer('*', function ($view) {
            $productos_agotar = StockProduct::whereColumn('stock_actual', '<=', 'stock_minimo')->count();
            $view->with('productos_agotar', $productos_agotar);
        });
    }

    public function register()
    {
        //
    }
}
