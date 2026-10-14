<?php

namespace App\Http\Requests;

use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('title'))) {
            $this->merge(['title' => Str::squish($this->input('title'))]);
        }
    }

    public function rules(): array
    {
        return [
            // VALIDACIÓN NUEVA 1: título único, ignorando mayúsculas y espacios repetidos.
            'title' => ['bail', 'required', 'string', 'min:5', 'max:150', function ($attribute, $value, $fail) {
                $query = Post::where('title_key', Str::lower($value));
                if ($post = $this->route('post')) {
                    $query->whereKeyNot($post->id);
                }
                if ($query->exists()) {
                    $fail('Ya existe una noticia con ese título.');
                }
            }],
            'excerpt' => ['required', 'string', 'min:20', 'max:300'],
            // VALIDACIÓN NUEVA 2: el contenido debe desarrollar la noticia, no repetir el resumen.
            'body' => ['required', 'string', 'min:80', 'max:20000', 'different:excerpt'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'status' => ['required', Rule::in(['draft', 'published'])],
        ];
    }

    public function attributes(): array
    {
        return ['title' => 'título', 'excerpt' => 'resumen', 'body' => 'contenido', 'category_id' => 'categoría', 'status' => 'estado'];
    }

    public function messages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.', 'string' => 'El campo :attribute debe ser texto.',
            'min' => 'El campo :attribute debe tener al menos :min caracteres.',
            'max' => 'El campo :attribute no debe superar :max caracteres.',
            'body.different' => 'El contenido debe ser diferente del resumen y ampliar la información.',
            'category_id.exists' => 'Selecciona una categoría existente.', 'category_id.integer' => 'La categoría no es válida.',
            'status.in' => 'Selecciona borrador o publicada.',
        ];
    }
}
