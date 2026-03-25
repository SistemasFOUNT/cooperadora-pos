<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
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
        $productId = $this->route('product')?->id;

        return [
            'code' => 'required|string|max:255|unique:products,code,' . $productId,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:product,service,fee,treatment',
            'category' => 'required|in:laboratory,dental_treatment,student_fee,postgraduate_fee,other',
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'track_stock' => 'boolean',
            'barcode' => 'nullable|string|max:255|unique:products,barcode,' . $productId,
            'additional_data' => 'nullable|array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'code.required' => 'El código del producto es obligatorio.',
            'code.unique' => 'Ya existe un producto con este código.',
            'name.required' => 'El nombre del producto es obligatorio.',
            'type.required' => 'El tipo de producto es obligatorio.',
            'type.in' => 'El tipo de producto seleccionado no es válido.',
            'category.required' => 'La categoría del producto es obligatoria.',
            'category.in' => 'La categoría seleccionada no es válida.',
            'price.required' => 'El precio es obligatorio.',
            'price.numeric' => 'El precio debe ser un número.',
            'price.min' => 'El precio no puede ser negativo.',
            'cost.numeric' => 'El costo debe ser un número.',
            'cost.min' => 'El costo no puede ser negativo.',
            'stock.required' => 'El stock es obligatorio.',
            'stock.integer' => 'El stock debe ser un número entero.',
            'stock.min' => 'El stock no puede ser negativo.',
            'min_stock.required' => 'El stock mínimo es obligatorio.',
            'min_stock.integer' => 'El stock mínimo debe ser un número entero.',
            'min_stock.min' => 'El stock mínimo no puede ser negativo.',
            'barcode.unique' => 'Ya existe un producto con este código de barras.',
        ];
    }
}
