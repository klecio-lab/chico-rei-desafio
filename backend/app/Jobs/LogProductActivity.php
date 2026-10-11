<?php

namespace App\Jobs;

use App\Models\ProductLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class LogProductActivity implements ShouldQueue
{
    use Queueable;

    /**
     * Log nunca é retentável: se falhar, registra e segue (quarentena
     * implícita). O CRUD jamais quebra por causa do log (§6 do AGENTS.md).
     */
    public int $tries = 1;

    public function __construct(
        private readonly ?int $productId,
        private readonly string $action,
        private readonly ?int $userId,
        private readonly ?array $changes,
    ) {}

    public function handle(): void
    {
        try {
            ProductLog::create([
                'product_id' => $this->productId,
                'action' => $this->action,
                'user_id' => $this->userId,
                'changes' => $this->changes,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Falha ao gravar log de produto.', [
                'action' => $this->action,
                'product_id' => $this->productId,
                'error' => mb_substr($e->getMessage(), 0, 2000),
            ]);
        }
    }
}
