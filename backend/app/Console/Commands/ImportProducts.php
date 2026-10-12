<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\Import\ProductImportFetcherInterface;
use Illuminate\Console\Command;

class ImportProducts extends Command
{
    protected $signature = 'products:import
        {--tema= : Tema da listagem (ex.: geek). Default em config/import.php}
        {--url= : URL de listagem explícita (ex.: /camiseta/). Ignora --tema}
        {--modelo= : Filtra por modelagem (ex.: babylook) ou gênero (M/F/U)}
        {--categoria= : Categoria gravada. Default: --tema}
        {--limit= : Máximo de produtos. Default em config/import.php}
        {--dry-run : Só mostra o que faria, sem persistir}';

    protected $description = 'Importa produtos do site da Chico Rei com upsert por source_url (BDR-009)';

    public function handle(ProductImportFetcherInterface $fetcher): int
    {
        if (! config('import.enabled')) {
            $this->error('Importação desabilitada (IMPORT_ENABLED=false). Nada foi feito.');

            return self::FAILURE;
        }

        $tema = $this->option('tema') ?: config('import.default_tema');
        $limit = $this->option('limit') !== null ? (int) $this->option('limit') : null;
        $dryRun = (bool) $this->option('dry-run');
        $url = $this->option('url');
        $modelo = $this->option('modelo');
        $categoria = $this->option('categoria') ?: $tema;

        $items = $url
            ? $fetcher->fetchUrl($url, $limit)
            : $fetcher->fetchTema($tema, $limit);

        if (is_string($modelo) && $modelo !== '') {
            $items = array_values(array_filter(
                $items,
                fn (array $item): bool => collect($item['models'] ?? [])
                    ->contains(fn (array $model): bool => $this->modelMatches($model, $modelo))
            ));
        }

        $items = array_slice($items, 0, $limit ?? config('import.default_limit'));

        $origem = $url ? "[{$url}]" : "tema [{$tema}]";

        if ($items === []) {
            $this->warn("Nenhum produto encontrado em {$origem}.");

            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->info('[dry-run] '.count($items)." produto(s) de {$origem} — nada persistido.");

            foreach ($items as $item) {
                $this->line("  - {$item['name']} | R$ ".number_format($item['price'], 2, ',', '.')." | {$item['source_url']}");
            }

            return self::SUCCESS;
        }

        // Reimportação atualiza SÓ price/image; stock/category entram na criação.
        Product::upsert(
            array_map(fn (array $item): array => [
                'name' => $item['name'],
                'description' => null,
                'image' => $item['image'],
                'price' => $item['price'],
                'category' => $categoria,
                'stock' => config('import.default_stock'),
                'source_url' => $item['source_url'],
            ], $items),
            uniqueBy: ['source_url'],
            update: ['price', 'image'],
        );

        $this->info(count($items)." produto(s) de {$origem} importados (upsert por source_url).");

        return self::SUCCESS;
    }

    /**
     * @param  array{url: string, gender: string}  $model
     */
    private function modelMatches(array $model, string $modelo): bool
    {
        return strcasecmp($model['url'], $modelo) === 0
            || strcasecmp($model['gender'], $modelo) === 0;
    }
}
