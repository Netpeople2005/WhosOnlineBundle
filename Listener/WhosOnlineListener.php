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
 * Clase con los metodos escuchas correspondientes para mantener
 * actualizado el WhosOnline.
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
     * @var WhosOnline
     */
    private $whosOnline;

    /**
     * @var LoggerInterface 
     */
    private $logger;

    /**
     * Indica si se deben registrar usuarios anonimos ó no
     * 
     * @var boolean 
     */
    protected $registerAnonymous;

    /**
     * Constructor...
     * @param SecurityContext $context
     * @param WhosOnline $whosOnline
     * @param LoggerInterface $logger 
     */
    public function __construct(SecurityContext $context, WhosOnline $whosOnline, LoggerInterface $logger, $registerAnonymous)
    {
        $this->context = $context;
        $this->whosOnline = $whosOnline;
        $this->logger = $logger;
        $this->registerAnonymous = $registerAnonymous;
    }

    /**
     * Este metodo se ejecutará en todas las peticiones hechas al servidor.
     * 
     * Aqui se registra una actividad en el whos_online para 
     * el usuario si está logueado.
     * 
     * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event 
     */
    public function onKernelResponse(\Symfony\Component\HttpKernel\Event\FilterResponseEvent $event)
    {
        $token = $this->context->getToken();
        $ip = $event->getRequest()->getClientIp();

        //verifico la existencia de un TOKEN
        if ($token instanceof TokenInterface) {
            //verifico si ya está registrado el token en whos_online
            if ($this->whosOnline->isOnline($token, $ip)) {
                //si ya está online, registro una actividad
                if ($this->whosOnline->registerActivity($token, $ip)) {
                    $this->logger->info("Se actualiza el WhosOnline del User {$token->getUsername()}");
                }
            } else {
                //si no está registrado aun, lo agrego al WhosOnline
                //si es un usuario anonimo
                if ($token instanceof \Symfony\Component\Security\Core\Authentication\Token\AnonymousToken) {
                    //lo agrego solo si está permitido el registro de usuarios anonimos.
                    if ($this->registerAnonymous && $this->whosOnline->registerOnline($token, $ip)) {
                        $this->logger->info("Registrando al Usuario {$token->getUsername()} en el WhosOnline");
                    }
                } else {
                    //si no es anonimo lo registro.
                    if ($this->whosOnline->registerOnline($token, $ip)) {
                        $this->logger->info("Registrando al Usuario {$token->getUsername()} en el WhosOnline");
                    }
                }
            }
        }
        //si no se ha hecho limpieza de la tabla, la hacemos.
        if (!$this->whosOnline->isClean()) {
            $this->whosOnline->clear();
        }
    }

    /**
     * Este metodo se ejecutará cuando un usuario inicie sesion en el sistema.
     * 
     * Aqui se crea un registro en la tabla whos_online con los datos
     * del usuario.
     * 
     * @param \Symfony\Component\Security\Http\Event\InteractiveLoginEvent $event 
     */
    public function onLogin(\Symfony\Component\Security\Http\Event\InteractiveLoginEvent $event)
    {
        $token = $this->context->getToken();
        $ip = $event->getRequest()->getClientIp();
        if ($token instanceof TokenInterface) {
            if ($this->whosOnline->registerOnline($token, $ip)) {
                $this->logger->info("Registrando al Usuario {$token->getUsername()} en el WhosOnline");
            }
        }
    }

    /**
     * Este metodo se ejecutará cuando un usuario cierre sesion en el sistema,
     * solo si ha establecido a este servicio como un escucha en
     * el parametro "handlers" del logout en el security.yml
     * 
     * Aqui se elimina el registro de la tabla whos_online que representa al
     * usuario conectado y que está cerrando sesión.
     *
     * @param Request $request
     * @param Response $response
     * @param TokenInterface $token 
     */
    public function logout(Request $request, Response $response, TokenInterface $token)
    {
        $ip = $request->getClientIp();

        if ($token instanceof TokenInterface) {

            if ($this->whosOnline->delete($token, $ip)) {
                $this->logger->info("Removido el Usuario {$token->getUsername()} del WhosOnline");
            }
        }
    }

}
