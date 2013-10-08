<?php

namespace Graviton\CoreBundle\Controller;

//use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\FOSRestController as Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AppController extends Controller
{
    
    /**
     * @Rest\View
     */
    public function allAction()
    {
    	return $this->getDoctrine()
    	    ->getRepository('GravitonCoreBundle:App')
    	    ->findAll();
    }
    
    /**
     * @Rest\View
     */
    public function getAction($id)
    {
    	return $this->getDoctrine()
    	    ->getRepository('GravitonCoreBundle:App')
    	    ->find($id);
    }
    
    /**
     * @Rest\View
     */
    public function newAction ()
    {
    	
    }
    
    /**
     * @Rest\View
     */
    public function editAction ()
    {
    	
    }
    
    /**
     * @Rest\View
     */
    public function removeAction ()
    {
    	
    }
}
