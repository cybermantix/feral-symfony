<?php

namespace Nodez\Inline\Process\NodeCode\NodeCodeSource;

use Nodez\Core\Process\Catalog\CatalogNode\CatalogNodeInterface;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

/**
 * A catalog source which is contains an array of catalogNode objects.
 */
class TaggedNodeCodeSource implements NodeCodeSourceInterface
{
    private array $nodeCodes;
    public function __construct(
        #[TaggedIterator('nodez.node_code')] iterable $nodeCodes
    ){
        foreach ($nodeCodes as $nodeCode) {
            $this->nodeCodes[] = $nodeCode;
        }
    }

    /**
     * @inheritDoc
     */
    public function getNodeCodes(): array
    {
        return $this->nodeCodes;
    }
}