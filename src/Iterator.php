<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Treap;

use Spiral\Treap\Node\PivotedNode;

/**
 * Iterates over given data-set and instantiates objects.
 */
final class Iterator implements \IteratorAggregate
{
    /** @var ORMInterface */
    private $orm;

    /** @var string */
    private $class;

    /** @var iterable */
    private $source;

    /**
     * @param ORMInterface $orm
     * @param string       $class
     * @param iterable     $source
     */
    public function __construct(ORMInterface $orm, string $class, iterable $source)
    {
        $this->orm = $orm;
        $this->class = $class;
        $this->source = $source;
    }

    /**
     * Generate over data. Pivoted data would be returned as key value if set.
     *
     * @return \Generator
     */
    public function getIterator(): \Generator
    {
        foreach ($this->source as $index => $data) {
            if (isset($data[PivotedNode::PIVOT_DATA])) {
                // when pivot data is provided we are going to use it as array key.
                $index = $data[PivotedNode::PIVOT_DATA];
                unset($data[PivotedNode::PIVOT_DATA]);
            }

            yield $index => $this->orm->makeEntity($this->class, $data, Heap::STATE_LOADED);
        }
    }
}