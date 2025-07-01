<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;
class CategoryUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [

        ];
    }

    public function message(): array{
         return [
            //
        ];
    }
}
