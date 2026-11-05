<?php

namespace App\Console\Commands;

use App\Services\MidtransClient;
use App\Services\QrisPaymentService;
use Illuminate\Console\Command;

class ReconcileQrisPayments extends Command
{
    protected $signature = 'payments:reconcile-qris {--limit=50}';
    protected $description = 'Reconcile overdue gateway QRIS orders against Midtrans sandbox status.';

    public function handle(MidtransClient $client): int
    {
        $result = (new QrisPaymentService($client))->reconcileDue((int) $this->option('limit'));

        $this->info(json_encode($result));

        return self::SUCCESS;
    }
}
