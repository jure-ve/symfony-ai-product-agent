<?php

namespace App\AI\Tool;

use App\Repository\ProductRepository;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;

#[AsTool(
    name: 'product_price',
    description: 'Returns the current price of a product given its SKU',
)]
final class ProductPriceTool
{
    public function __construct(
        private readonly ProductRepository $productRepository,
    ) {}

    /**
     * @param string $sku The product SKU identifier
     */
    public function __invoke(string $sku): string
    {
        $product = $this->productRepository->findBySku($sku);

        if ($product === null) {
            return sprintf('No product found with SKU "%s".', $sku);
        }

        return sprintf(
            'The product "%s" (SKU: %s) costs %.2f EUR.',
            $product->getName(),
            $sku,
            $product->getPrice(),
        );
    }
}