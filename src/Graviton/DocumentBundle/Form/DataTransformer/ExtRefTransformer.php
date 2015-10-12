<?php
/**
 * ExtRefTransformer class file
 */

namespace Graviton\DocumentBundle\Form\DataTransformer;

use Graviton\DocumentBundle\Entity\ExtReference;
use Graviton\DocumentBundle\Service\ExtReferenceConverterInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ExtRefTransformer implements DataTransformerInterface
{
    /**
     * @var ExtReferenceConverterInterface
     */
    private $converter;

    /**
     * Constructor
     *
     * @param ExtReferenceConverterInterface $converter Ext reference converter
     */
    public function __construct(ExtReferenceConverterInterface $converter)
    {
        $this->converter = $converter;
    }

    /**
     * Transforms an object (extref) to a string (url)
     *
     * @param  ExtReference|null $extref extref object
     * @return string
     */
    public function transform($extref)
    {
        if ($extref === null) {
            return '';
        }

        try {
            return $this->converter->getUrl($extref);
        } catch (\InvalidArgumentException $e) {
            throw new TransformationFailedException(
                sprintf('Cannot transform extref "%s" to URL', json_encode($extref)),
                0,
                $e
            );
        }
    }

    /**
     * Transforms a string (url) to an object (extref)
     *
     * @param string $url extref url
     * @return ExtReference|null
     * @throws TransformationFailedException if url is not valid
     */
    public function reverseTransform($url)
    {
        if ($url === '' || $url === null) {
            return null;
        }

        try {
            return $this->converter->getExtReference($url);
        } catch (\InvalidArgumentException $e) {
            throw new TransformationFailedException(
                sprintf('Cannot transform URL "%s" to extref', $url),
                0,
                $e
            );
        }
    }
}
