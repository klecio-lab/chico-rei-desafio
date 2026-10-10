<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class IndexProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'in_stock' => ['nullable', 'boolean'],
            'sort' => ['nullable', 'string', 'in:name,-name,price,-price,stock,-stock,created_at,-created_at'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'q.string' => 'A busca deve ser um texto.',
            'q.max' => 'A busca deve ter no máximo :max caracteres.',
            'category.string' => 'A categoria deve ser um texto.',
            'category.max' => 'A categoria deve ter no máximo :max caracteres.',
            'min_price.numeric' => 'O preço mínimo deve ser um número.',
            'min_price.min' => 'O preço mínimo não pode ser negativo.',
            'max_price.numeric' => 'O preço máximo deve ser um número.',
            'max_price.min' => 'O preço máximo não pode ser negativo.',
            'in_stock.boolean' => 'O filtro de estoque deve ser verdadeiro ou falso.',
            'sort.in' => 'Ordenação inválida. Use name, price, stock ou created_at (prefixo - inverte).',
            'per_page.integer' => 'A quantidade por página deve ser um número inteiro.',
            'per_page.min' => 'A quantidade por página deve ser ao menos :min.',
            'per_page.max' => 'A quantidade por página deve ser no máximo :max.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $min = $validator->getData()['min_price'] ?? null;
            $max = $validator->getData()['max_price'] ?? null;

            if ($min !== null && $max !== null && is_numeric($min) && is_numeric($max) && $max < $min) {
                $validator->errors()->add('max_price', 'O preço máximo deve ser maior ou igual ao preço mínimo.');
            }
        });
    }
}
