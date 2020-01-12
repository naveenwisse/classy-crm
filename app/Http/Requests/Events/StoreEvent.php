<?php

namespace App\Http\Requests\Events;

use Froiden\LaravelInstaller\Request\CoreRequest;
use Illuminate\Foundation\Http\FormRequest;

class StoreEvent extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
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
        return [
            'event_name' => 'required_if:event_type,1|required_if:event_type,2',
            'start_date' => 'required',
            'end_date' => 'required',
            'user_id' => 'required',
            'lead_id' => 'required_if:event_type,1',
            'project_id' => 'required_if:event_type,2'
        ];
    }

    public function messages() {
        return [
            'event_name.required_if' => __('Appointment name field is required.'),
            'project_id.required_if' => __('Project field is required.'),
            'lead_id.required_if' => __('Lead field is required.')
        ];
    }
}
