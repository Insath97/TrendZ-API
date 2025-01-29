<?php

namespace App\Console\Commands;

use App\Models\Slot;
use Illuminate\Console\Command;

class ResetSlot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset-slot';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resets all slots to recurring for the new day';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Slot::query()->update(['is_recurring' => true]);
        $this->info('All slots have been reset for the new day.');
    }
}
