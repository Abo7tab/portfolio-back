<?php
namespace App\Domains\Portfolio\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePersonalInfoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name'           => 'sometimes|string|max:255',
            'job_title'           => 'sometimes|string|max:255',
            'bio'                 => 'nullable|string',
            'theme_color'         => 'nullable|string|max:20',
            'profile_image'       => 'sometimes|nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'phone'               => 'nullable|string|max:20',
            'email'               => 'nullable|email',
            'cv_web_file'         => 'sometimes|nullable|file|mimes:pdf|max:10240',
            'cv_network_file'     => 'sometimes|nullable|file|mimes:pdf|max:10240',
            'cv_web_btn_text'     => 'sometimes|nullable|string|max:100',
            'cv_network_btn_text' => 'sometimes|nullable|string|max:100',
            'years_experience'    => 'nullable|integer|min:0',
            'completed_projects'  => 'nullable|integer|min:0',
            'satisfied_customers' => 'nullable|integer|min:0',
        ];
    }
}
