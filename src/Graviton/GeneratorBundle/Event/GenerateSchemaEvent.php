<?php
/**
 * GenerateSchemaEvent
 */

namespace Graviton\GeneratorBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
final class GenerateSchemaEvent extends Event
{
    /**
     * our event name
     *
     * @var string
     */
    const string EVENT_NAME = 'generate.global.schema';

    /**
     * @var array schemas
     */
    private array $addedSchemas = [];

    /**
     * adds a single openapi schema
     *
     * @param array $schema
     *
     * @return void
     */
    public function addSingleSchema(array $schema) : void
    {
        $this->addedSchemas[] = $schema;
    }

    public function getAdditionalSchemas() : array
    {
        return $this->addedSchemas;
    }
}
