<?php

namespace App\Http\Requests\Post;

use Illuminate\Foundation\Http\FormRequest;
class PostStoreRequest extends FormRequest
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
