<?php

namespace Netpeople\WhosOnlineBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{
    
    public function indexAction($name)
    {
        return $this->render('WhosOnlineBundle:Default:index.html.twig', array('name' => $name));
    }
}
