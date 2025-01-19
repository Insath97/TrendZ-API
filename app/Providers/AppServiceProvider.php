<?php

namespace App\Providers;

use App\Models\BookingSlots;
use App\Observers\BookingSlotObserver;
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
        BookingSlots::observe(BookingSlotObserver::class);
    }
}
