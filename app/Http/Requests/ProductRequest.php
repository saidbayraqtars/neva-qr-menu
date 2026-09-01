<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999'],
            'discount_price' => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'is_available' => ['boolean'],
            'is_featured' => ['boolean'],
            'calories' => ['nullable', 'integer', 'min:0', 'max:20000'],
            'tags' => ['nullable', 'string', 'max:255'],
            'allergens' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:'.config('neva.uploads.image_max_kb')],
        ];
    }

    public function preparedTags(): ?array
    {
        return $this->filled('tags')
            ? collect(explode(',', $this->input('tags')))->map(fn ($t) => trim($t))->filter()->values()->all()
            : null;
    }

    public function preparedAllergens(): ?array
    {
        return $this->filled('allergens')
            ? collect(explode(',', $this->input('allergens')))->map(fn ($t) => trim($t))->filter()->values()->all()
            : null;
    }
}
