<?php
/**
 * Part of JSON definition: "target.fields.x-dynamic-key"
 */
namespace Graviton\GeneratorBundle\Definition\Schema;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class XDynamicKey
{
    /**
     * @var string
     */
    private $documentId;

    /**
     * @var string
     */
    private $repositoryMethod;

    /**
     * @var string
     */
    private $recordGetter;

    /**
     * @return string
     */
    public function getDocumentId()
    {
        return $this->documentId;
    }

    /**
     * @param string $documentId document-id field
     * @return $this
     */
    public function setDocumentId($documentId)
    {
        $this->documentId = $documentId;
        return $this;
    }

    /**
     * @return string
     */
    public function getRepositoryMethod()
    {
        return $this->repositoryMethod;
    }

    /**
     * @param string $repositoryMethod repository-method field
     * @return $this
     */
    public function setRepositoryMethod($repositoryMethod)
    {
        $this->repositoryMethod = $repositoryMethod;
        return $this;
    }

    /**
     * @return string
     */
    public function getRecordGetter()
    {
        return $this->recordGetter;
    }

    /**
     * @param string $recordGetter record-getter field
     * @return $this
     */
    public function setRecordGetter($recordGetter)
    {
        $this->recordGetter = $recordGetter;
        return $this;
    }
}
