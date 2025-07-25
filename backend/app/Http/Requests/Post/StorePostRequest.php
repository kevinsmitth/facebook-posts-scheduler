<?php

namespace App\Http\Requests\Post;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:100',
            'content' => 'required|string|max:280',
            'scheduled_for' => 'nullable|date|after:'.now('America/Sao_Paulo')->format('Y-m-d H:i:s'),
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'O título é obrigatório.',
            'title.max' => 'O título deve ter no máximo 100 caracteres.',
            'content.required' => 'O conteúdo é obrigatório.',
            'content.max' => 'O conteúdo deve ter no máximo 280 caracteres.',
            'scheduled_for.required' => 'A data de agendamento é obrigatória.',
            'scheduled_for.after' => 'A data de agendamento deve ser no futuro.',
            'image.image' => 'O arquivo deve ser uma imagem.',
            'image.mimes' => 'A imagem deve ser do tipo: jpeg, png, jpg, gif.',
            'image.max' => 'A imagem deve ter no máximo 5MB.',
        ];
    }
}
