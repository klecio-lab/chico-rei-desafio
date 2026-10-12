<?php

namespace Tests\Unit;

use App\Services\Import\ChicoReiParser;
use Tests\TestCase;

class ChicoReiParserTest extends TestCase
{
    private function fixture(): string
    {
        return file_get_contents(base_path('tests/Fixtures/chicorei_tema.html'));
    }

    public function test_extrai_itens_validos_do_json_embutido(): void
    {
        $items = ChicoReiParser::parseListing($this->fixture(), 'https://chicorei.com');

        $this->assertCount(2, $items);

        $this->assertSame('Camiseta Fixture Alfa', $items[0]['name']);
        $this->assertSame(49.9, $items[0]['price']);
        $this->assertSame('https://chicorei.imgix.net/fixture/alfa.jpg', $items[0]['image']);
        $this->assertSame('https://chicorei.com/camiseta/fixture-alfa-101.html', $items[0]['source_url']);
    }

    public function test_usa_fallback_de_imagem_e_ignora_colchetes_no_texto(): void
    {
        $items = ChicoReiParser::parseListing($this->fixture(), 'https://chicorei.com');

        // img_male vazio => cai para img_female; nome com [ ] e { } não quebra o scanner.
        $this->assertSame('Camiseta Fixture Beta [Edição {Especial}]', $items[1]['name']);
        $this->assertSame('https://chicorei.imgix.net/fixture/beta.jpg', $items[1]['image']);
    }

    public function test_ignora_itens_sem_nome_ou_link(): void
    {
        $items = ChicoReiParser::parseListing($this->fixture(), 'https://chicorei.com');

        foreach ($items as $item) {
            $this->assertNotEmpty($item['name']);
            $this->assertNotEmpty($item['source_url']);
        }
    }

    public function test_html_sem_json_retorna_vazio(): void
    {
        $this->assertSame([], ChicoReiParser::parseListing('<html><body>nada</body></html>', 'https://chicorei.com'));
    }

    public function test_extrai_models_de_cada_item(): void
    {
        $items = ChicoReiParser::parseListing($this->fixture(), 'https://chicorei.com');

        $this->assertSame(
            [['url' => 'regular', 'gender' => 'M'], ['url' => 'babylook', 'gender' => 'F']],
            $items[0]['models']
        );
        $this->assertSame([['url' => 'regular', 'gender' => 'M']], $items[1]['models']);
    }
}
