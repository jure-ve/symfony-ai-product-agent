<?php

namespace App\AI\Tool;

use App\Repository\ProductRepository;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;
use Symfony\Component\Validator\Constraints as Assert;

#[AsTool(
    name: 'search',
    description: 'Searches products by category slug. Returns up to 3 products per page.',
)]
final class ProductSearchTool
{
    public function __construct(
        private readonly ProductRepository $productRepository,
    ) {}

    /**
     * @param string $category Category slug to filter by (e.g. "telefono", "portatil", "auriculares", "reloj")
     * @param string $page     Page number as string, defaults to "1". Use "2" for more results.
     */
    public function __invoke(
        string $category, 
        #[Assert\GreaterThanOrEqual(1)]
        string $page = '1'
    ): string
    {
        $limit   = 3;
        $pageInt = max(1, (int) $page);
        $offset  = ($pageInt - 1) * $limit;

        $products = $this->productRepository->searchByCategory($category, $limit, $offset);

        if (empty($products)) {
            return sprintf('No se encontraron productos en la categoría "%s" (página %d).', $category, $pageInt);
        }

        $result = sprintf('Categoría "%s" — Página %d:' . "\n", $category, $pageInt);
        foreach ($products as $product) {
            $result .= sprintf(
                "- %s (SKU: %s) — %.2f EUR\n",
                $product->getName(),
                $product->getSku(),
                $product->getPrice()
            );
        }

        return $result;
    }
}
