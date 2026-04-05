<?php

namespace App\Repository;

use App\Entity\Product;

class ProductRepository
{
    /** @var Product[] */
    private array $products;

    public function __construct()
    {
        // Simple mock in-memory data for the prototype
        $this->products = [
            new Product('SKU-100', 'Smartphone X', 799.99),
            new Product('SKU-200', 'Laptop Pro 15', 1299.50),
            new Product('SKU-300', 'Wireless Headphones', 199.00),
            new Product('SKU-400', 'Smartwatch Series 5', 249.99),
        ];
    }

    public function findBySku(string $sku): ?Product
    {
        foreach ($this->products as $product) {
            if ($product->getSku() === $sku) {
                return $product;
            }
        }

        return null;
    }
}
