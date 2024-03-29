<?php

namespace App\Api\V1\Requests;

use Config;
use Dingo\Api\Http\FormRequest;

class FinanceAccountRequest extends FormRequest
{
    public function rules()
    {
        return Config::get('boilerplate.finance-account.validation_rules');
    }

    public function authorize()
    {
        return true;
    }
}
