<?php

namespace Netpeople\WhosOnlineBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{

    public function indexAction()
    {
        $usuariosConectados = $this->get('whos_online')->getOnlineUsers();

        return $this->render('WhosOnlineBundle:Default:index.html.twig', array(
                    'onlines' => $usuariosConectados
                ));
    }

}
