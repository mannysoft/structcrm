<?php

declare(strict_types=1);

namespace App\Common\ValueObjects;

use InvalidArgumentException;

final readonly class Money
{
    public function __construct(
        public int $amountInCents,
        public string $currency = 'USD'
    ) {
        if ($amountInCents < 0) {
            throw new InvalidArgumentException('Amount cannot be negative.');
        }
    }

    public static function fromDecimal(float $amount, string $currency = 'USD'): self
    {
        return new self((int) round($amount * 100), $currency);
    }
    
    public function toDecimal(): float
    {
        return $this->amountInCents / 100;
    }
}
