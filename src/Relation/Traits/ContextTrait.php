<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\ORM\Relation\Traits;

use Spiral\ORM\Command\ContextualInterface;
use Spiral\ORM\Command\ScopedInterface;
use Spiral\ORM\State;

/**
 * Provides the ability to set the promises for command context and scopes linked
 * to related entity state change.
 */
trait ContextTrait
{
    /**
     * Configure context parameter using value from parent entity. Created promise.
     *
     * @param ContextualInterface $command
     * @param State               $parent
     * @param string              $parentKey
     * @param null|State          $current
     * @param string              $localKey
     */
    protected function promiseContext(
        ContextualInterface $command,
        State $parent,
        string $parentKey,
        ?State $current,
        string $localKey
    ) {
        $handler = function (State $state) use ($command, $localKey, $parentKey, $current) {
            if (empty($value = $this->fetchKey($state, $parentKey))) {
                return false;
            }

            if ($this->fetchKey($current, $localKey) != $value) {
                $command->setContext($localKey, $value);
                if (!is_null($current)) {
                    $current->setData([$localKey => $value]);
                }
            }

            $command->freeContext($localKey);
            return true;
        };

        // optimizing this will save a lot of memory
        $command->waitContext($localKey, $this->isRequired());
        call_user_func($handler, $parent);
        $parent->addListener($handler);
    }

    /**
     * Configure where parameter in scoped command based on key provided by the
     * parent entity. Creates promise.
     *
     * @param ScopedInterface $command
     * @param State           $parent
     * @param string          $parentKey
     * @param null|State      $current
     * @param string          $localKey
     */
    protected function promiseScope(
        ScopedInterface $command,
        State $parent,
        string $parentKey,
        ?State $current,
        string $localKey
    ) {
        $handler = function (State $state) use ($command, $localKey, $parentKey, $current) {
            if (empty($value = $this->fetchKey($state, $parentKey))) {
                return false;
            }

            if ($this->fetchKey($current, $localKey) != $value) {
                $command->setScope($localKey, $value);
            }

            $command->freeScope($localKey);
            return true;
        };

        // optimizing this will save a lot of memory
        $command->waitScope($localKey, $this->isRequired());
        call_user_func($handler, $parent);
        $parent->addListener($handler);
    }

    /**
     * Fetch key from the state.
     *
     * @param State  $state
     * @param string $key
     * @return mixed|null
     */
    protected function fetchKey(?State $state, string $key)
    {
        if (is_null($state)) {
            return null;
        }

        return $state->getData()[$key] ?? null;
    }

    /**
     * True is given relation is required for the object to be saved (i.e. NOT NULL).
     *
     * @return bool
     */
    abstract public function isRequired(): bool;
}