<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\CommonRule;

class CreditRequest extends FormRequest
{
    
    public $commonRuleInstance;

    public function __construct(CommonRule $commonRule){
        $this->commonRuleInstance = $commonRule;
    }
   
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
        return array_merge($this->commonRuleInstance->commonRules(),[
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
        return array_merge($this->commonRuleInstance->commonMessages());
    }

    public function isRequestValid(){
        return $this->getValidatorInstance()->fails()?FALSE:TRUE;
    }

    public function validateSuccess($extra_response){
        return $this->commonRuleInstance->validateSuccess($extra_response);
    }

    public function testFields(){
        return $this->commonRuleInstance->testFields();
    }
}
