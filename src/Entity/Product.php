<?php

namespace App\Entity;

class Product
{
    public function __construct(
        private readonly string $sku,
        private readonly string $name,
        private readonly float $price,
    ) {}

    public function getSku(): string
    {
        return $this->sku;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): float
    {
        return $this->price;
    }
}
