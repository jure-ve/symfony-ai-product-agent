<?php

namespace App\AI\Tool;

use App\Repository\ProductRepository;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;

#[AsTool(
    name: 'categories',
    description: 'Returns the list of available product categories or types in the store.',
)]
final class ProductCategoryTool
{
    public function __construct(
        private readonly ProductRepository $productRepository,
    ) {}

    public function __invoke(string $_ = ''): string
    {
        $categories = $this->productRepository->getCategories();
        
        return 'The available product categories are: ' . implode(', ', $categories);
    }
}
