<?php

namespace App\Http\Requests\Api;

class SearchRecipeRequest extends ApiFormRequest
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
            'author_email' => 'sometimes|nullable|email|max:255',
            'keyword' => 'sometimes|nullable|string|max:100|min:2',
            'ingredient' => 'sometimes|nullable|string|max:100|min:2',
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:5|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'author_email.email' => 'Please enter a valid email address',
            'keyword.min' => 'Search keyword must be at least 2 characters',
            'ingredient.min' => 'Ingredient search term must be at least 2 characters',
            'per_page.max' => 'You can request a maximum of 50 recipes per page',
        ];
    }
}
