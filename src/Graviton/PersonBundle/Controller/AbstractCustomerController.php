<?php
/**
 * abstract customer controller
 *
 * Implements async reading and writing for static data.
 *
 * We have heaps of 'unchangeable' data coming from complex enterprisy systems that have some data we need
 * want to let our users change nevertheless. This controller implements a diff-layer to that data and
 * allows us to store differences to the mainframe locally.
 *
 * We do this by adding an additional collection to mongodb containing an overlay of diff records. The idea
 * is that there is also tooling that enables us to write back the contents of this diff layer into the
 * enterprisy data container at a later stage following the varying complex scenarios needed to get that data
 * back into the mainframe.
 */

namespace Graviton\PersonBundle\Controller;

use Graviton\RestBundle\Controller\RestController;
use Graviton\PersonBundle\Repository\CustomerDiffRepository;
use Graviton\RestBundle\Service\RestUtilsInterface;
use Graviton\I18nBundle\Repository\LanguageRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactory;
use Graviton\DocumentBundle\Form\Type\DocumentType;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
abstract class AbstractCustomerController extends RestController
{
    /**
     * @var CustomerDiffRepository
     */
    private $diffRepo;

    /**
     * @param Response               $response    Response
     * @param RestUtilsInterface     $restUtils   Rest utils
     * @param Router                 $router      Router
     * @param LanguageRepository     $language    Language
     * @param ValidatorInterface     $validator   Validator
     * @param EngineInterface        $templating  Templating
     * @param FormFactory            $formFactory form factory
     * @param DocumentType           $formType    generic form
     * @param ContainerInterface     $container   Container
     * @param CustomerDiffRepository $diffRepo    repo containing customer diffs
     */
    public function __construct(
        Response $response,
        RestUtilsInterface $restUtils,
        Router $router,
        LanguageRepository $language,
        ValidatorInterface $validator,
        EngineInterface $templating,
        FormFactory $formFactory,
        DocumentType $formType,
        ContainerInterface $container,
        CustomerDiffRepository $diffRepo
    ) {
        parent::__construct(
            $response,
            $restUtils,
            $router,
            $language,
            $validator,
            $templating,
            $formFactory,
            $formType,
            $container
        );
        $this->diffRepo = $diffRepo;
    }
}
