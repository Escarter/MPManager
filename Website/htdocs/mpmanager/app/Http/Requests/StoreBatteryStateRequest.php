<?php


namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;

class StoreBatteryStateRequest extends FormRequest
{

    public function authorize():bool  {
        return true;
    }

    public function rules(): array  {
        return [
            'mini_grid_id' => 'required',
            'node_id' => 'required',
            'device_id' => 'required',
            'batteries' => 'required',
            'read_out' => 'required',
        ];
    }
}
