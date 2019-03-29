<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

class CreditRequest extends FormRequest
{
    
    use CommonRule;
   
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return array_merge($this->commonRules(),[
            'transactionId' => 'required|string',
            'amount' => 'required|numeric',
            'type' => 'required|string',
            'creditIndex' => 'integer'
        ]);
    }

    /**
     * Custom message for validation
     *
     * @return array
     */
    public function messages()
    {
        return array_merge($this->commonMessages());
    }

    public function isRequestValid(){
        return $this->getValidatorInstance()->fails()?FALSE:TRUE;
    }

}
