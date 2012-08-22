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

    public function onKernelRequest(\Symfony\Component\HttpKernel\Event\GetResponseEvent $event)
    {
        //obtengo la instanacia del token
        $token = $this->context->getToken();
        //si es una instancia de UsernamePasswordToken es que hay un user logueado.
        if ($token instanceof \Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken) {

            $this->logger->info("registrando actividad en WhosOnline con Usuario Logueado");

            $user = $token->getUser();
            $this->logger->info("El usuario {$user->getUsername()} realizó una petición");


            $this->em->getRepository('WhosOnlineBundle:WhosOnline')
                    ->createQueryBuilder('w')
                    ->update()->set('w.lastActivity', new DateTime())
                    ->where('w.user = :user')
                    ->setParameter('user', $user)
                    ->getQuery()->execute();
//
//                $whosOnline = $this->em->getRepository('WhosOnlineBundle:WhosOnline')
//                        ->createQueryBuilder('w')
//                        ->where('w.user = :user')
//                        ->setParameter('user', $user)
//                        ->getQuery()
//                        ->getSingleResult();
//                if ( $whosOnline instanceof WhosOnline){
//                    $whosOnline->setLastActivity(new \DateTime());
//                    $this->em->persist($whosOnline);
//                    $this->em->flush();
//                }
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

            $whosOnline = new WhosOnline($user, $ip);

            $this->em->persist($whosOnline);
            $this->em->flush();

            $this->logger->info("Registrando al Usuario {$user->getUsername()} en el WhosOnline");
        } else {
            
        }
    }

}
