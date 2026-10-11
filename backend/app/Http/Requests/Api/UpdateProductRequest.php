<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Catálogo coletivo: todo autenticado pode escrever (sem dono, sem policy).
        return true;
    }

    /**
     * PUT é atualização total: mesmas regras do store, explícitas por
     * operação (§0.2 do AGENTS.md — um FormRequest por operação).
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'string', 'url', 'max:2048'],
            'price' => ['required', 'numeric', 'min:0'],
            'category' => ['required', 'string', 'max:100'],
            'stock' => ['required', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'name.string' => 'O nome deve ser um texto.',
            'name.min' => 'O nome deve ter ao menos :min caracteres.',
            'name.max' => 'O nome deve ter no máximo :max caracteres.',
            'description.string' => 'A descrição deve ser um texto.',
            'image.string' => 'A imagem deve ser um texto.',
            'image.url' => 'A imagem deve ser uma URL válida.',
            'image.max' => 'A imagem deve ter no máximo :max caracteres.',
            'price.required' => 'O preço é obrigatório.',
            'price.numeric' => 'O preço deve ser um número.',
            'price.min' => 'O preço não pode ser negativo.',
            'category.required' => 'A categoria é obrigatória.',
            'category.string' => 'A categoria deve ser um texto.',
            'category.max' => 'A categoria deve ter no máximo :max caracteres.',
            'stock.required' => 'O estoque é obrigatório.',
            'stock.integer' => 'O estoque deve ser um número inteiro.',
            'stock.min' => 'O estoque não pode ser negativo.',
        ];
    }
}
