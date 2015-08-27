<?php
/**
 * EmbedOne class file
 */

namespace Graviton\DocumentBundle\DependencyInjection\Compiler\Utils;

/**
 * Embed one field
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class EmbedOne extends AbstractField
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
     */
    public function __construct(Document $document, $fieldName, $exposedName, $readOnly)
    {
        $this->document = $document;
        parent::__construct($fieldName, $exposedName, $readOnly);
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
