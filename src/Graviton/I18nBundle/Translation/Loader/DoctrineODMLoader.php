<?php
/**
 * Load translation string from a doctrine-odm repository
 */

namespace Graviton\I18nBundle\Translation\Loader;

use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Symfony\Component\Config\Resource\FileResource;

/**
 * Load translation string from a doctrine-odm repository
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class DoctrineODMLoader implements LoaderInterface
{
    /**
     * @var DocumentRepository
     */
    private $repository;

    /**
     * set repo
     *
     * @param DocumentRepository $repository repo
     *
     * @return void
     */
    public function setRepository(DocumentRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * get repo
     *
     * @return DocumentRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * {@inheritDoc}
     *
     * @param mixed  $resource resource
     * @param string $locale   locale name
     * @param string $domain   message domain
     *
     * @return MessageCatalogue
     */
    public function load($resource, $locale, $domain = 'messages')
    {
        $repository = $this->getRepository();
        $messages = $repository->findBy(array('domain' => $domain, 'locale' => $locale));

        $catalogue = new MessageCatalogue($locale);

        array_walk(
            $messages,
            function ($message) use ($catalogue) {
                $catalogue->set((string) $message->getOriginal(), $message->getTranslated(), $message->getDomain());
            }
        );

        $catalogue->addResource(new FileResource($resource));

        return $catalogue;
    }
}
