<?php
/**
 * dummy "showcase" module
 */

namespace Graviton\CoreBundle\Controller;

use Graviton\RestBundle\Controller\RestController;
use Symfony\Component\HttpFoundation\Response;

/**
 * This is just a dummy controller for demonstrating
 * the extension of generated bundles..
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ShowcaseExtensionController extends RestController
{

    /**
     * IMPORTANT NOTES IF YOU INHERIT FROM DYNAMICALLY GENERATED BUNDLE!
     *
     * Remember that if you want to change data and/or add fields; that those
     * fields must be present:
     * 1) in the originating Document
     * 2) in the serializer configuration
     * 3) maybe include validation rules in validation.xml
     *
     * it's not the right way to let something generate it and then completely change it's
     * structure. if you need something that you cannot generate so that you have to modify
     * *everything* when inheriting - don't generate it in the first place! ;-)
     *
     * the basic workflow is to define the entire structure in the json file, then here
     * (overriding) you *only* modify and alter data - don't add new properties.
     *
     * @return \Symfony\Component\HttpFoundation\Response $response Response with result or error
     */
    public function allAction()
    {
        // to just get parent reponse
        // $response = parent::allAction();

        $data = $this->getModel()->findAll($this->getRequest());

        $response = $this->getResponse()
            ->setStatusCode(Response::HTTP_OK)
            ->setContent($this->serialize($data));

        return $this->render(
            'GravitonCoreBundle:Main:index.json.twig',
            array('response' => $response->getContent()),
            $response
        );
    }
}
