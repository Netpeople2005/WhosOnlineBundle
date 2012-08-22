<?php

namespace Netpeople\WhosOnlineBundle\Listener;

use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Bundle\DoctrineBundle\Registry;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Netpeople\WhosOnlineBundle\Entity\WhosOnline;

//use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Description of RequestListener
 *
 * @author maguirre
 */
class WhosOnlineListener //implements EventSubscriberInterface
{

    /**
     * @var \Symfony\Component\Security\Core\SecurityContext 
     */
    private $context;

    /**
     * @var \Doctrine\ORM\EntityManager 
     */
    private $em;

    /**
     * @var LoggerInterface 
     */
    private $logger;

    /**
     * Constructor
     *
     * @param SecurityContext $context
     * @param Doctrine $doctrine
     */
    public function __construct(SecurityContext $context, Registry $doctrine, LoggerInterface $logger)
    {
        $this->context = $context;
        $this->em = $doctrine->getEntityManager();
        $this->logger = $logger;
    }

//    public static function getSubscribedEvents()
//    {
//        return array(
//            'kernel.request' => array('onKernelRequest', 0),
//            'security.interactive_login' => array('onLogin', 0),
//        );
//    }

    public function onKernelResponse(\Symfony\Component\HttpKernel\Event\FilterResponseEvent $event)
    {
        //obtengo la instanacia del token
        $token = $this->context->getToken();
        //si es una instancia de UsernamePasswordToken es que hay un user logueado.
        if ($token instanceof \Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken) {

            $this->logger->info("registrando actividad en WhosOnline con Usuario Logueado");

            //obtengo la instancia del usuario conectado.
            /* @var $user \Symfony\Component\Security\Core\User\UserInterface */
            $user = $token->getUser();
            $this->logger->info("El usuario {$user->getUsername()} realizó una petición");

            //consulto el registro en WhosOnline para el usuario conectado.
            $whosOnline = $this->em->getRepository('WhosOnlineBundle:WhosOnline')
                    ->findOneByUsername($user->getUsername());

            //y si existe dicho usuario en WhosOnline, actualizo su lastActivity.
            if ($whosOnline instanceof WhosOnline) {
                $whosOnline->setLastActivity(new \DateTime());
                $this->em->persist($whosOnline);
                $this->em->flush();
                $this->logger->info("Se actualiza el WhosOnline a {$whosOnline->getLastActivity()->format(\DateTime::W3C)}");
            }
        }
    }

    /**
     * 
     *
     * @param Event $event
     */
    public function onLogin(\Symfony\Component\Security\Http\Event\InteractiveLoginEvent $event)
    {
        /* @var $user \Symfony\Component\Security\Core\User\UserInterface */
        $user = $this->context->getToken()->getUser();
        $ip = $event->getRequest()->getClientIp();

        if ($user instanceof \Symfony\Component\Security\Core\User\UserInterface) {

            $whosOnline = new WhosOnline($user->getUsername(), $ip);

            $this->em->persist($whosOnline);
            $this->em->flush();

            $this->logger->info("Registrando al Usuario {$user->getUsername()} en el WhosOnline");
        } else {
            
        }
    }

}
