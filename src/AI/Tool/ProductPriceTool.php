<?php

namespace App\AI\Tool;

use App\Repository\ProductRepository;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;

#[AsTool(
    name: 'price',
    description: 'Returns the exact price of a single product using its SKU identifier.',
)]
final class ProductPriceTool
{
    public function __construct(
        private readonly ProductRepository $productRepository,
    ) {}

    /**
     * @param string $id The product SKU (e.g. "SKU-100")
     */
    public function __invoke(string $id): string
    {
        $product = $this->productRepository->findBySku($id);

        if ($product === null) {
            return sprintf('No encontré el producto con SKU "%s".', $id);
        }

        return sprintf(
            'El producto "%s" (SKU: %s) cuesta %.2f EUR.',
            $product->getName(),
            $id,
            $product->getPrice()
        );
    }
}