<?php

final class ApiException extends RuntimeException
{
    public function __construct(
        public readonly int $status,
        string $message,
        public readonly ?string $errorCode = null,
        public readonly array $meta = []
    ) {
        parent::__construct($message);
    }
}