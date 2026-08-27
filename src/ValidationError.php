<?php

declare(strict_types=1);

namespace Egensolve\Realtime\Contracts;






final class ValidationError extends \RuntimeException
{
     
     
    


    private function __construct(
        public readonly ValidationErrorCode $errorCode,
        public readonly ValidationErrorCategory $category,
        string $message,
        public readonly ?string $path = null,
        public readonly array $details = [],
    ) {
        parent::__construct($message);
    }

    


    public static function make(
        ValidationErrorCode $code,
        ValidationErrorCategory $category,
        string $message,
        ?string $path = null,
        array $details = [],
    ): self {
        return new self($code, $category, $message, $path, $details);
    }

    


    public function toArray(): array
    {
        $result = [
            'code' => $this->errorCode->value,
            'category' => $this->category->value,
            'message' => $this->getMessage(),
        ];
        if ($this->path !== null) {
            $result['path'] = $this->path;
        }
        if ($this->details !== []) {
            $result['details'] = $this->details;
        }

        return $result;
    }
}
