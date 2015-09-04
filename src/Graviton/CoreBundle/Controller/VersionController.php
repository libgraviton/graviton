<?php
/**
 * Controller for core/version endpoint
 */

namespace Graviton\CoreBundle\Controller;

use Graviton\DocumentBundle\Form\Type\DocumentType;
use Graviton\RestBundle\Controller\RestController;
use Graviton\RestBundle\Service\RestUtilsInterface;
use Graviton\SchemaBundle\SchemaUtils;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class VersionController extends RestController
{

    /**
     * @var string path to cache dir
     */
    private $cacheDir = '';

    /**
     * @param Response           $response    Response
     * @param RestUtilsInterface $restUtils   Rest utils
     * @param Router             $router      Router
     * @param ValidatorInterface $validator   Validator
     * @param EngineInterface    $templating  Templating
     * @param FormFactory        $formFactory form factory
     * @param DocumentType       $formType    generic form
     * @param ContainerInterface $container   Container
     * @param SchemaUtils        $schemaUtils schema utils
     * @param string             $cacheDir    cache directory
     */
    public function __construct(
        Response $response,
        RestUtilsInterface $restUtils,
        Router $router,
        ValidatorInterface $validator,
        EngineInterface $templating,
        FormFactory $formFactory,
        DocumentType $formType,
        ContainerInterface $container,
        SchemaUtils $schemaUtils,
        $cacheDir
    ) {
        parent::__construct(
            $response,
            $restUtils,
            $router,
            $validator,
            $templating,
            $formFactory,
            $formType,
            $container,
            $schemaUtils
        );
        $this->cacheDir = $cacheDir;
    }

    /**
     * Returns all version numbers
     *
     * @param Request $request Current http request
     *
     * @return \Symfony\Component\HttpFoundation\Response $response Response with result or error
     */
    public function allAction(Request $request)
    {
        $versions['versions'] = json_decode(file_get_contents($this->cacheDir.'/swagger/versions.json'));

        $response = $this->getResponse()
            ->setStatusCode(Response::HTTP_OK);

        return $this->render(
            'GravitonRestBundle:Main:index.json.twig',
            ['response' => json_encode($versions)],
            $response
        );
    }
}
