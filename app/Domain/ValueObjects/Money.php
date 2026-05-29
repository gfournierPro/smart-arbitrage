<?php

namespace App\Domain\ValueObjects;


// in cents
final readonly class Money 
{
    public function __construct( 
        public int $cents,
        public string $currency = 'EUR')
    {}

    public static function fromEuros(float $euros, string $currency ='EUR'): self {
        return new self((int) round($euros*100), $currency);
    }

    public function toEuros(): float
    {
        return $this->cents / 100;
    }

    public function add(Money $other): self 
    {
        $this->assertSameCurrency($other);
        return new self($this->cents + $other->cents, $this->currency);
    }

    private function assertSameCurrency(Money $other): void
    {
        if($this->currency != $other->currency){
            throw new \InvalidArgumentException('Devises incompatibles.');
        }
    }


}