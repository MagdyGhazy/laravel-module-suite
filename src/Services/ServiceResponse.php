<?php

namespace Ghazym\LaravelModuleSuite\Services;

class ServiceResponse
{
    public function __construct(public int $status, public mixed $data = null, public ?string $message = null, public mixed $errors = null)
    {

    }

    public static function success(mixed $data, string $message = 'Success', int $status = 200): self
    {
        return new self($status, $data, $message);
    }

    public static function error(string $message, int $status = 400, mixed $errors = null): self
    {
        return new self($status, null, $message, $errors);
    }
}