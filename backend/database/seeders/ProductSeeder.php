<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Catálogo demo determinístico: ~20 produtos variando categoria, preço
        // e estoque — incluindo zerados para exercitar in_stock=false (BDR-006:
        // image é URL placeholder, sem upload).
        $products = [
            ['Camiseta Clássica Preta', 'camisetas', 59.90, 25, 'Algodão 100%, modelagem reta.'],
            ['Camiseta Estampa Galáxia', 'camisetas', 69.90, 12, 'Estampa exclusiva frente e verso.'],
            ['Camiseta Básica Branca', 'camisetas', 49.90, 0, 'O essencial de todo guarda-roupa.'],
            ['Camiseta Listrada Marinho', 'camisetas', 64.90, 8, null],
            ['Camiseta Oversized Areia', 'camisetas', 74.90, 15, 'Modelagem ampla, gola careca.'],
            ['Camiseta Regata Cinza', 'camisetas', 44.90, 0, 'Ideal para treino.'],
            ['Caneca Cerâmica 350ml', 'canecas', 34.90, 40, 'Cerâmica fosca, 350ml.'],
            ['Caneca Térmica Inox', 'canecas', 89.90, 10, 'Mantém 6h quente / 12h frio.'],
            ['Caneca Mágica Preta', 'canecas', 39.90, 0, 'Revela a estampa com líquido quente.'],
            ['Caneca Esmaltada Branca', 'canecas', 29.90, 22, null],
            ['Moletom Canguru Grafite', 'moletons', 129.90, 7, 'Flanelado interno, capuz duplo.'],
            ['Moletom Careca Verde', 'moletons', 119.90, 0, 'Sem capuz, punho ribana.'],
            ['Moletom Zip Preto', 'moletons', 139.90, 5, 'Fechamento em zíper, 2 bolsos.'],
            ['Boné Aba Curva', 'acessorios', 54.90, 18, 'Ajuste snapback.'],
            ['Meia Cano Alto (3 pares)', 'acessorios', 39.90, 30, null],
            ['Ecobag Algodão Cru', 'acessorios', 24.90, 0, 'Alça longa, 30L.'],
            ['Pin Metálico Sortido', 'acessorios', 14.90, 100, 'Pacote com 3 unidades.'],
            ['Camiseta Manga Longa Vinho', 'camisetas', 79.90, 9, 'Meia-estação.'],
            ['Caneca Vidro 400ml', 'canecas', 32.90, 14, 'Vidro borossilicato.'],
            ['Moletom Canguru Bege', 'moletons', 129.90, 3, 'Mesma modelagem do grafite.'],
        ];

        foreach ($products as [$name, $category, $price, $stock, $description]) {
            Product::create([
                'name' => $name,
                'description' => $description,
                'image' => 'https://placehold.co/600x600?text='.urlencode($name),
                'price' => $price,
                'category' => $category,
                'stock' => $stock,
            ]);
        }
    }
}
