<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTournamentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'game' => ['sometimes', 'string', 'max:100'],
            'date' => ['sometimes', 'date', 'after:today'],
        ];
    }
}
