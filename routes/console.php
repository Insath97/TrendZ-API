<?php

use App\Console\Commands\ResetSlot;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command(ResetSlot::class)->daily();
