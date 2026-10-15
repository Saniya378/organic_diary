<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckDairyBatchExpiries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dairy:check-expiries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan organic dairy products for batch expiration and dispatch freshness alerts to sellers';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Scanning organic dairy inventory for expiring & expired batches...');

        $products = \App\Models\Product::whereNotNull('expires_at')
            ->where('status', 'approved')
            ->get();

        $expiredCount = 0;
        $expiringSoonCount = 0;

        foreach ($products as $product) {
            $daysLeft = $product->days_until_expiry;

            if ($daysLeft !== null && $daysLeft <= 2) {
                $product->checkAndDispatchExpiryAlert();

                if ($daysLeft < 0) {
                    $expiredCount++;
                    $this->warn("Expired: [{$product->id}] {$product->name} (Expired on {$product->expires_at->format('Y-m-d')})");
                } else {
                    $expiringSoonCount++;
                    $this->comment("Expiring soon ({$daysLeft}d left): [{$product->id}] {$product->name}");
                }
            }
        }

        $this->info("Completed batch expiry check: {$expiredCount} expired, {$expiringSoonCount} expiring soon.");

        return 0;
    }
}
