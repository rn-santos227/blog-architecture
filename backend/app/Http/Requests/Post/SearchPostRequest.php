<?php

namespace App\Http\Requests\Post;

use Illuminate\Foundation\Http\FormRequest;

class SearchPostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'min:2', 'required_without:tags'],

            'page' => ['nullable', 'integer', 'min:1'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],

            'author_id' => ['nullable', 'integer', 'exists:users,id'],
            'tag' => ['nullable', 'string', 'min:2'],
            'tags' => ['nullable', 'array', 'required_without:q'],
            'tags.*' => ['string', 'min:2'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $tags = $this->input('tags');
        if (is_string($tags)) {
            $tags = array_filter(array_map('trim', explode(',', $tags)));
        }

        if (empty($tags) && $this->filled('tag')) {
            $tags = [$this->input('tag')];
        }

        $this->merge([
            'page'  => $this->input('page', 1),
            'limit' => $this->input('limit', 10),
            'tags' => $tags,
        ]);
    }
}
