<?php
/**
 * Created by PhpStorm.
 * Date: 2022-08-07
 * Time: 22:43
 */

namespace App\Console\Commands;

use App\Services\OrderService;
use Illuminate\Console\Command;

class DiscountCalculationCommand extends Command
{
    protected $signature = 'shopbe:discount-calculation {userId} {percent}';

    protected $description = "Discount calculation for crontab";

    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        parent::__construct();

        $this->orderService = $orderService;
    }

    public function handle()
    {
        $userId = (float)$this->argument('userId');
        $percent = (float)$this->argument('percent');

        $err = $this->orderService->calculateDiscount($userId, $percent);

        if ($err !== NULL){
            $this->error($err->getMessage());
        }
        else{
            $this->info('Discount Calculated');
        }
    }
}
