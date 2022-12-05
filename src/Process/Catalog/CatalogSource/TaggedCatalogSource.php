<?php
namespace Nodez\Inline\Process\Catalog\CatalogSource;

use Nodez\Core\Process\Catalog\CatalogNode\CatalogNodeInterface;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

/**
 * A catalog source which is contains an array of catalogNode objects.
 */
class TaggedCatalogSource implements CatalogSourceInterface
{
    private array $catalogNodes;
    public function __construct(
        #[TaggedIterator('nodez.catalog_node')] iterable $catalogNodes
    ){
        foreach ($catalogNodes as $node) {
            $this->catalogNodes[] = $node;
        }
    }

    /**
     * @inheritDoc
     */
    public function getCatalogNodes(): array
    {
        return $this->catalogNodes;
    }
}