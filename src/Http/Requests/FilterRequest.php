<?php
namespace Naimul007A\LaravelBaseKit\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FilterRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array {
        return [
            'search'  => 'nullable|string|max:255',
            'page'    => 'nullable|integer|min:1',
            'limit'   => 'nullable|integer|min:1',
            'sort'    => 'nullable|string|max:255',
            'order'   => 'nullable|string|in:asc,desc',
            'filters' => 'nullable|array',
        ];
    }
}
