<?php
/**
 * ExtReferenceJsonConverter class file
 */

namespace Graviton\DocumentBundle\Service;

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
     * @var ExtReferenceConverterInterface
     */
    private $refConverter;

    /**
     * @param ExtReferenceConverterInterface $refConverter
     */
    public function __construct(ExtReferenceConverterInterface $refConverter)
    {
        $this->refConverter = $refConverter;
    }

    /**
     * @param mixed $data
     * @param array $fields
     * @return array
     */
    public function convert($data, $fields)
    {
        if (is_array($data)) {
            foreach ($data as $index => $row) {
                $data[$index] = $this->mapItem($row, $fields);
            }
        } elseif (is_object($data)) {
            $data = $this->mapItem($data, $fields);
        }

        return $data;
    }

    /**
     * apply single mapping
     *
     * @param mixed $item item to apply mapping to
     * @param array $fields
     *
     * @return array
     */
    private function mapItem($item, array $fields)
    {
        foreach ($fields as $field) {
            $item = $this->mapField($item, $field);
        }

        return $item;
    }

    /**
     * recursive mapper for embed-one fields
     *
     * @param mixed  $item  item to map
     * @param string $field name of field to map
     *
     * @return array
     */
    private function mapField($item, $field)
    {
        if (is_array($item)) {
            if ($field === '0') {
                $item = array_map([$this, 'convertToUrl'], $item);
            } elseif (strpos($field, '0.') === 0) {
                $subField = substr($field, 2);
                $item = array_map(
                    function ($subItem) use ($subField) {
                        return $this->mapField($subItem, $subField);
                    },
                    $item
                );
            }
        } elseif (is_object($item)) {
            if (($pos = strpos($field, '.')) !== false) {
                $topLevel = substr($field, 0, $pos);
                $subField = substr($field, $pos + 1);
                if (isset($item->$topLevel)) {
                    $item->$topLevel = $this->mapField($item->$topLevel, $subField);
                } else {
                    // map available things since we found nothing on $topLevel and there might be some refs deeper down
                    foreach ($item as $subLevel => $subItem) {
                        $item->$subLevel = $this->mapField($subItem, $subField);
                    }
                 }
            } elseif (isset($item->$field)) {
                $item->$field = $this->convertToUrl($item->$field);
            }
        }
        return $item;
    }

    /**
     * Convert extref to URL
     *
     * @param string $ref JSON encoded extref
     * @return string
     */
    private function convertToUrl($ref)
    {
        try {
            $ref = json_decode($ref);
            return $this->refConverter->getUrl($ref);
        } catch (\InvalidArgumentException $e) {
                return '';
        }
    }

}