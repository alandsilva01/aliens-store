<?php
namespace App\Http\Requests;

use App\Rules\AllowedHtmlTags;
use App\Rules\SalePriceRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'       => 'required|string|max:255',
            'description' => ['required', 'string', new AllowedHtmlTags],
            'sale_price'  => ['required', 'numeric', 'min:0', new SalePriceRule($this->cost_price)],
            'cost_price'  => 'required|numeric|min:0',
            'category'    => 'nullable|string|max:100',
            'images'      => 'nullable|array|max:10',
            'images.*'    => 'file|mimes:jpg,jpeg,png|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'       => 'O titulo e obrigatorio.',
            'description.required' => 'A descricao e obrigatoria.',
            'sale_price.required'  => 'O preco de venda e obrigatorio.',
            'cost_price.required'  => 'O custo e obrigatorio.',
            'images.*.mimes'       => 'Apenas imagens JPG e PNG sao permitidas.',
            'images.*.max'         => 'Cada imagem pode ter no maximo 5MB.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json(['message' => 'Erro de validacao.', 'errors' => $validator->errors()], 422)
        );
    }
}
