<?php

namespace Netpeople\WhosOnlineBundle\Listener;

use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Netpeople\WhosOnlineBundle\Services\WhosOnline;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Description of RequestListener
 *
 * @author maguirre
 */
class WhosOnlineListener implements LogoutHandlerInterface
{

    /**
     * @var \Symfony\Component\Security\Core\SecurityContext 
     */
    private $context;

    /**
     * @var \Doctrine\ORM\EntityManager 
     */
    private $whosOnline;

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
    public function __construct(SecurityContext $context, WhosOnline $whosOnline, LoggerInterface $logger)
    {
        $this->context = $context;
        $this->whosOnline = $whosOnline;
        $this->logger = $logger;
    }

    public function onKernelResponse(\Symfony\Component\HttpKernel\Event\FilterResponseEvent $event)
    {
        $token = $this->context->getToken();
        $ip = $event->getRequest()->getClientIp();

        if ($token instanceof TokenInterface) {
            if ($this->whosOnline->registerActivity($token, $ip)) {
                $this->logger->info("Se actualiza el WhosOnline del User {$token->getUsername()}");
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
        $token = $this->context->getToken();
        $ip = $event->getRequest()->getClientIp();

        if ($token instanceof TokenInterface) {

            if($this->whosOnline->registerOnline($token, $ip)) {
                $this->logger->info("Registrando al Usuario {$token->getUsername()} en el WhosOnline");
            }
        }
    }

    public function logout(Request $request, Response $response, TokenInterface $token)
    {
        $ip = $event->getRequest()->getClientIp();

        if ($token instanceof TokenInterface) {

            if($this->whosOnline->delete($token, $ip)) {
                $this->logger->info("Removido el Usuario {$token->getUsername()} del WhosOnline");
            }
        }
    }

}
