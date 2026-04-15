<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'document' => 'nullable|string|max:2',
            'document_number' => 'nullable|string|max:50',
            'nrc' => 'nullable|string|max:20',
            'activity_id' => 'nullable|exists:economic_activities,id',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'retains_iva' => 'boolean',
            'country_id' => 'required|exists:countries,id',
            'municipality_id' => 'nullable|exists:municipalities,id',
        ];

        // Si es NIT, hacer NRC y actividad económica requeridos
        if ($this->document == '36') {
            $rules['nrc'] = 'required|string|max:20';
            $rules['activity_id'] = 'required|exists:economic_activities,id';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'nrc.required' => 'El NRC es requerido cuando el tipo de documento es NIT',
            'activity_id.required' => 'La Actividad Económica es requerida cuando el tipo de documento es NIT',
            'activity_id.exists' => 'Por favor seleccione una Actividad Económica válida',
        ];
    }
}
