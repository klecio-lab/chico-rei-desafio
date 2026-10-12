<?php

namespace App\Console\Commands;

use App\Services\Search\ElasticProductSearch;
use Illuminate\Console\Command;

class ReindexProducts extends Command
{
    protected $signature = 'products:reindex';

    protected $description = 'Recria o índice do Elasticsearch e indexa o catálogo (BDR-012)';

    public function handle(ElasticProductSearch $search): int
    {
        if (! config('search.elastic_enabled')) {
            $this->error('Busca ES desabilitada (ELASTIC_ENABLED=false). Nada foi feito.');

            return self::FAILURE;
        }

        $result = $search->reindex();

        $this->info("{$result['indexed']} produto(s) indexados.");

        return self::SUCCESS;
    }
}
