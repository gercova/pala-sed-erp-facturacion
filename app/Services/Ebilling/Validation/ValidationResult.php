<?php

namespace App\Services\Ebilling\Validation;

class ValidationResult
{
    private array $errors = [];

    public function addError(string $field, string $message): void
    {
        $this->errors[] = [
            'field' => $field,
            'message' => $message,
        ];
    }

    public function isValid(): bool
    {
        return $this->errors === [];
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
