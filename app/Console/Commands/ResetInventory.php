<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Inventory\InventoryBalance;
use App\Models\Inventory\InventoryMovement;

class ResetInventory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark all stock 0, and remove inventory history';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting inventory reset...');

        // Remove inventory history (movements and logs)
        InventoryMovement::truncate();
        $this->info('Removed all inventory movements (history).');

        // Mark all stock as 0
        InventoryBalance::query()->update(['quantity' => 0]);
        $this->info('Marked all existing inventory balances to 0.');

        $this->info('Inventory reset complete.');
    }
}
