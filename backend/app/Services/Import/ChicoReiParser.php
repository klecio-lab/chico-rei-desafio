<?php

namespace App\Services\Import;

/**
 * Extrai produtos do JSON embutido (`"products":{"hits":[...]}`) na listagem
 * do site. Parser puro (sem HTTP, sem banco): testável com fixture (BDR-009).
 */
class ChicoReiParser
{
    /**
     * @return array<int, array{name: string, price: float, image: ?string, source_url: string}>
     */
    public static function parseListing(string $html, string $baseUrl): array
    {
        $arrayStart = self::findHitsArrayStart($html);

        if ($arrayStart === null) {
            return [];
        }

        $decoded = json_decode(self::extractBalancedArray($html, $arrayStart), true);

        if (! is_array($decoded)) {
            return [];
        }

        $items = [];

        foreach ($decoded as $raw) {
            $item = self::normalizeItem($raw, $baseUrl);

            if ($item !== null) {
                $items[] = $item;
            }
        }

        return $items;
    }

    private static function findHitsArrayStart(string $html): ?int
    {
        $keyPos = strpos($html, '"hits":[');

        return $keyPos === false ? null : $keyPos + strlen('"hits":');
    }

    /**
     * Recorta o array JSON a partir do `[` respeitando strings/escapes —
     * o bloco pode conter chaves e colchetes dentro de textos.
     */
    private static function extractBalancedArray(string $html, int $start): string
    {
        $depth = 0;
        $inString = false;
        $escaped = false;
        $length = strlen($html);

        for ($i = $start; $i < $length; $i++) {
            $char = $html[$i];

            if ($inString) {
                if ($escaped) {
                    $escaped = false;
                } elseif ($char === '\\') {
                    $escaped = true;
                } elseif ($char === '"') {
                    $inString = false;
                }

                continue;
            }

            if ($char === '"') {
                $inString = true;
            } elseif ($char === '[') {
                $depth++;
            } elseif ($char === ']') {
                $depth--;

                if ($depth === 0) {
                    return substr($html, $start, $i - $start + 1);
                }
            }
        }

        return '';
    }

    /**
     * @return array{name: string, price: float, image: ?string, source_url: string}|null
     */
    private static function normalizeItem(mixed $raw, string $baseUrl): ?array
    {
        if (! is_array($raw)) {
            return null;
        }

        $name = isset($raw['name']) && is_string($raw['name']) ? trim($raw['name']) : '';
        $link = isset($raw['link']) && is_string($raw['link']) ? trim($raw['link']) : '';

        if ($name === '' || $link === '' || ! isset($raw['price']) || ! is_numeric($raw['price'])) {
            return null;
        }

        return [
            'name' => $name,
            // Duas casas desde a origem: float cru vira ruído binário no JSON.
            'price' => round((float) $raw['price'], 2),
            'image' => self::pickImage($raw, $baseUrl),
            'source_url' => self::absoluteUrl($link, $baseUrl),
        ];
    }

    /**
     * @param  array<string, mixed>  $raw
     */
    private static function pickImage(array $raw, string $baseUrl): ?string
    {
        foreach (['img_male', 'img_female', 'img_thumb'] as $key) {
            if (isset($raw[$key]) && is_string($raw[$key]) && $raw[$key] !== '') {
                return self::absoluteUrl($raw[$key], $baseUrl);
            }
        }

        return null;
    }

    private static function absoluteUrl(string $url, string $baseUrl): string
    {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return rtrim($baseUrl, '/').'/'.ltrim($url, '/');
    }
}
