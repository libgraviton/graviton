<?php
/**
 * ExtReferenceJsonConverter class file
 */

namespace Graviton\DocumentBundle\Service;
use Symfony\Component\Routing\RouterInterface;

/**
 * Extref converter
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ExtReferenceJsonConverter implements ExtReferenceJsonConverterInterface
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var array
     */
    private $mapping;

    /**
     * @var array
     */
    private $fields;

    /**
     * Constructor
     *
     * @param RouterInterface $router
     * @param array $mapping
     * @param array $fields
     */
    public function __construct(RouterInterface $router, array $mapping, array $fields)
    {
        $this->router = $router;
        $this->mapping = $mapping;
        $this->fields = $fields;
    }

    /**
     * @param array $data
     * @param string $routeId
     * @return array
     */
    public function convert(array $data, $routeId)
    {
        if (is_array($data) && !empty($data) && !is_string(array_keys($data)[0])) {
            foreach ($data as $index => $row) {
                $data[$index] = $this->mapItem($row, $routeId);
            }
        } else {
            $data = $this->mapItem($data, $routeId);
        }

        return $data;
    }

    /**
     * apply single mapping
     *
     * @param array $item item to apply mapping to
     * @param string $routeId
     *
     * @return array
     */
    private function mapItem(array $item, $routeId)
    {
        if (!array_key_exists($routeId, $this->fields)) {
            return $item;
        }
        foreach ($this->fields[$routeId] as $field) {
            $item = $this->mapField($item, $field);
        }

        return $item;
    }

    /**
     * recursive mapper for embed-one fields
     *
     * @param array  $item  item to map
     * @param string $field name of field to map
     *
     * @return array
     */
    private function mapField($item, $field)
    {
        if (!is_array($item)) {
            return $item;
        }

        if (strpos($field, '0.') === 0) {
            $subField = substr($field, 2);

            return array_map(
                function ($subItem) use ($subField) {
                    return $this->mapField($subItem, $subField);
                },
                $item
            );
        }

        if (($pos = strpos($field, '.')) !== false) {
            $topLevel = substr($field, 0, $pos);
            $subField = substr($field, $pos + 1);

            if (isset($item[$topLevel])) {
                $item[$topLevel] = $this->mapField($item[$topLevel], $subField);
            }
            return $item;
        }

        if (isset($item[$field])) {
            $ref = json_decode($item[$field], true);
            $routeId = $this->mapping[$ref['$ref']];
            $item[$field] = $this->router->generate($routeId, ['id' => $ref['$id']], true);
        }

        return $item;
    }

}