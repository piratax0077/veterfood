<?php

namespace App\Providers;

use App\Support\FormulariosAdmin;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        Schema::defaultStringLength(191);

        // Modales de "crear" del administrador: cada uno recibe sus datos solo cuando se incluye en una pagina
        View::composer('admin.modales.nueva-mascota', fn ($view) => $view->with('clientesMascota', FormulariosAdmin::clientesMascota()));
        View::composer('admin.modales.nuevo-voucher', fn ($view) => $view->with('datosVoucher', FormulariosAdmin::datosVoucher()));
        View::composer('admin.modales.nuevo-local', fn ($view) => $view->with('tiposLocal', FormulariosAdmin::tiposLocal()));
        View::composer('admin.modales.nuevo-usuario-rol', fn ($view) => $view->with('localesAsignables', FormulariosAdmin::localesAsignables()));
    }
}
