<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

class PlayRequest extends FormRequest
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
            'betAmount' => 'required|numeric',
            'winAmount' => 'required|numeric',
            'selections' => 'required|integer',
            'betPerSelection' => 'required|numeric',
            'freeGames' => 'required|boolean',
            'round' => 'required|integer',
            'roundsRemaining' => 'required|integer'
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
