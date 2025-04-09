<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\OrderStatus;
use App\Services\RedProviderService;
use Illuminate\Console\Command;

class SyncOrderStatuses extends Command
{
    protected $signature = 'app:sync-order-statuses';
    protected $description = 'Checks the RedService for any updates on the status of running orders';

    protected RedProviderService $redProviderService;

    public function __construct(RedProviderService $redProviderService)
    {
        parent::__construct();
        $this->redProviderService = $redProviderService;
    }

    public function handle(): void
    {
        $this->info('Starting order status synchronization...');

        $orders = Order::whereIn('status', [OrderStatus::PROCESSING, OrderStatus::ORDERED])->get();

        foreach ($orders as $order) {
            try {
                $status = $this->redProviderService->getOrderStatus($order['red_id']);
                if ($status) {
                    $order->status = $status;
                    $order->save();
                    $this->info("Order {$order->id} status updated to {$status}.");
                }
            } catch (\Exception $e) {
                $this->error("Failed to sync order {$order->id}: {$e->getMessage()}");
            }
        }
    }
}
