<?php
/**
 * EmbedMany class file
 */

namespace Graviton\DocumentBundle\DependencyInjection\Compiler\Utils;

/**
 * Embed many field
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class EmbedMany extends AbstractField
{
    /**
     * @var Document
     */
    private $document;

    /**
     * Constructor
     *
     * @param Document $document    Document type
     * @param string   $fieldName   Field name
     * @param string   $exposedName Exposed name
     * @param bool     $readOnly    Read only
     * @param bool     $required    Is required
     */
    public function __construct(Document $document, $fieldName, $exposedName, $readOnly, $required)
    {
        $this->document = $document;
        parent::__construct($fieldName, $exposedName, $readOnly, $required);
    }

    /**
     * Get document
     *
     * @return Document
     */
    public function getDocument()
    {
        return $this->document;
    }
}
