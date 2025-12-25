<?php

namespace App\Console\Commands;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CompleteOrders extends Command
{
    
    protected $signature = 'app:complete-orders';

    protected $description = 'Command description';

    public function handle()
    {
    $today = Carbon::today();

        $updated = Order::where('status', 'confirmed')
            ->where('check_out_date', '<', $today)
            ->update([
                'status' => 'completed'
            ]);

        $this->info("Completed {$updated} expired orders.");     
    }
}
