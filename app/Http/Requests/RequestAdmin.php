<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class RequestAdmin extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->adalahAdmin()
            ?? false;
    }
}
