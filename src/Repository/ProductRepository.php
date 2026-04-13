<?php

namespace App\Repository;

use App\Entity\Product;

class ProductRepository
{
    private array $products;

    public function __construct()
    {
        // Cada producto tiene su categoría normalizada (slug).
        // En una DB real esto sería una columna indexada o FK a tabla de categorías.
        $this->products = [
            // telefono
            new Product('SKU-100', 'Teléfono X',              799.99, 'telefono'),
            new Product('SKU-101', 'Teléfono Y Lite',         499.50, 'telefono'),
            new Product('SKU-102', 'Teléfono Z Pro Max',     1099.00, 'telefono'),
            new Product('SKU-103', 'Eco Teléfono',            299.00, 'telefono'),
            new Product('SKU-104', 'Teléfono Gaming Ultra',   899.99, 'telefono'),

            // portatil
            new Product('SKU-200', 'Portátil Pro 15',        1299.50, 'portatil'),
            new Product('SKU-201', 'Ultrabook Zen 13',        950.00, 'portatil'),
            new Product('SKU-202', 'Portátil Gaming RTX',    1599.99, 'portatil'),
            new Product('SKU-203', 'Portátil Estudiante',     350.00, 'portatil'),
            new Product('SKU-204', 'Portátil Creador 17',    1899.00, 'portatil'),

            // auriculares
            new Product('SKU-300', 'Auriculares Inalámbricos', 199.00, 'auriculares'),
            new Product('SKU-301', 'Auriculares Noise Cancel',  149.99, 'auriculares'),
            new Product('SKU-302', 'Auriculares de Estudio',    299.50, 'auriculares'),
            new Product('SKU-303', 'Auriculares Deportivos',     59.99, 'auriculares'),
            new Product('SKU-304', 'Auriculares Audiófilo',     450.00, 'auriculares'),

            // reloj
            new Product('SKU-400', 'Reloj Inteligente Serie 5', 249.99, 'reloj'),
            new Product('SKU-401', 'Reloj Deportivo Básico',     49.50, 'reloj'),
            new Product('SKU-402', 'Reloj Inteligente Premium', 399.00, 'reloj'),
            new Product('SKU-403', 'Reloj GPS para Niños',       89.99, 'reloj'),
            new Product('SKU-404', 'Reloj Todoterreno',         299.50, 'reloj'),
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

    /**
     * Busca productos por categoría (slug exacto) con paginación.
     * Equivalente a: SELECT * FROM products WHERE category = ? LIMIT ? OFFSET ?
     */
    public function searchByCategory(string $category, int $limit = 3, int $offset = 0): array
    {
        // Normalizar: minúsculas + sin tildes para comparación robusta
        $search = $this->normalize($category);

        $matches = array_filter($this->products, function (Product $p) use ($search) {
            return $this->normalize($p->getCategory()) === $search;
        });

        return array_slice(array_values($matches), $offset, $limit);
    }

    /**
     * Devuelve los slugs de categoría disponibles.
     * El modelo usa estos valores directamente como parámetro de búsqueda.
     */
    public function getCategories(): array
    {
        return array_unique(
            array_map(fn(Product $p) => $p->getCategory(), $this->products)
        );
    }

    private function normalize(string $value): string
    {
        return str_replace(
            ['á','é','í','ó','ú','ü','ñ'],
            ['a','e','i','o','u','u','n'],
            mb_strtolower(trim($value))
        );
    }
}
