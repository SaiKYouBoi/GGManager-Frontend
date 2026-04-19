<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTournamentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'             => ['required', 'string', 'max:255'],
            'game'             => ['required', 'string', 'max:100'],
            'date'             => ['required', 'date', 'after:today'],
            'max_participants' => ['required', 'integer', 'in:4,8,16,32'],
            'format'           => ['required', 'in:single_elimination'],
        ];
    }
}
