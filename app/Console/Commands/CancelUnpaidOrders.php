<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CancelUnpaidOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:cancel-unpaid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancel pending_payment orders older than 30 minutes.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $cutoffTime = Carbon::now()->subMinutes(30);

        $unpaidOrders = Order::where('status', 'pending_payment')
            ->where('created_at', '<=', $cutoffTime)
            ->with(['orderGroup', 'items'])
            ->get();

        if ($unpaidOrders->isEmpty()) {
            $this->info('No expired pending_payment orders found.');
            return Command::SUCCESS;
        }

        $cancelledCount = 0;

        foreach ($unpaidOrders as $order) {
            DB::transaction(function () use ($order, &$cancelledCount) {
                $order->update([
                    'status' => 'cancelled',
                    'payment_status' => 'failed',
                    'cancel_reason' => 'Payment session expired after 30 minutes without completion.',
                ]);

                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status' => 'cancelled',
                    'note' => 'System automatically cancelled order due to payment expiration (30 minutes).',
                    'changed_by' => $order->buyer_id,
                ]);

                if ($order->orderGroup) {
                    Payment::where('order_group_id', $order->order_group_id)
                        ->where('status', 'pending')
                        ->update(['status' => 'failed']);
                }

                $cancelledCount++;
            });
        }

        $this->info("Successfully cancelled {$cancelledCount} expired pending_payment orders.");
        Log::info("Cancelled {$cancelledCount} expired pending_payment orders.");

        return Command::SUCCESS;
    }
}
