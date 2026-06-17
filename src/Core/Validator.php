<?php
namespace App\Core;

class Validator {
    private array $errors = [];

    public function required(string $field, $value, string $label = ''): self {
        if (empty($value)) {
            $this->errors[$field] = ($label ?: $field) . ' este obligatoriu.';
        }
        return $this;
    }

    public function numeric(string $field, $value, string $label = ''): self {
        if (!is_numeric($value)) {
            $this->errors[$field] = ($label ?: $field) . ' trebuie să fie numeric.';
        }
        return $this;
    }

    public function min(string $field, $value, float $min, string $label = ''): self {
        if ((float)$value < $min) {
            $this->errors[$field] = ($label ?: $field) . " trebuie să fie minim $min.";
        }
        return $this;
    }

    public function max(string $field, $value, float $max, string $label = ''): self {
        if ((float)$value > $max) {
            $this->errors[$field] = ($label ?: $field) . " trebuie să fie maxim $max.";
        }
        return $this;
    }

    public function inArray(string $field, $value, array $allowed, string $label = ''): self {
        if (!in_array($value, $allowed, true)) {
            $this->errors[$field] = ($label ?: $field) . ' nu este o valoare permisă.';
        }
        return $this;
    }

    public function email(string $field, $value): self {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = 'Email invalid.';
        }
        return $this;
    }

    public function hasErrors(): bool {
        return !empty($this->errors);
    }

    public function getErrors(): array {
        return $this->errors;
    }
}