<?php

namespace Netpeople\WhosOnlineBundle\Services;

use Symfony\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Netpeople\WhosOnlineBundle\Entity\WhosOnline as MyEntity;

/**
 * Description of WhosOnline
 *
 * @author maguirre
 */
class WhosOnline
{

    /**
     * @var \Doctrine\ORM\EntityManager 
     */
    private $em;

    public function __construct(Registry $em)
    {
        $this->em = $em->getEntityManager();
    }

    public function registerOnline(TokenInterface $token, $ip)
    {
        $whosOnline = $this->em->getRepository('WhosOnlineBundle:WhosOnline')
                ->findOneBy(array(
            'username' => $token->getUsername(),
            'ip' => $ip,
                ));

        //verifico si ya existe en la tabla
        if ($whosOnline instanceof MyEntity) {
            //si existe lo que hago es actualizar :-)
            return $this->update($token, $ip);
        } else {
            //si no existe creo el registro
            $whosOnline = new MyEntity($token->getUsername(), $ip);

            $this->em->persist($whosOnline);
            $this->em->flush();

            return TRUE;
        }
    }

    public function registerActivity(TokenInterface $token, $ip)
    {

        //consulto el registro en WhosOnline para el usuario conectado.
        $whosOnline = $this->em->getRepository('WhosOnlineBundle:WhosOnline')
                ->findOneBy(array(
            'username' => $token->getUsername(),
            'ip' => $ip,
                ));

        //y si existe dicho usuario en WhosOnline, actualizo su lastActivity.
        if ($whosOnline instanceof MyEntity) {
            $whosOnline->setLastActivity(new \DateTime());
            $this->em->persist($whosOnline);
            $this->em->flush();

            return TRUE;
        }
    }

    public function delete(TokenInterface $token, $ip)
    {
        $this->em->createQuery("
                DELETE WhosOnlineBundle:WhosOnline w 
                WHERE w.username = :username AND w.ip = :ip
            ")->execute(array(
            'username' => $token->getUsername(),
            'ip' => $ip,
        ));

        return TRUE;
    }

    public function deleteOffline()
    {
        $last = new \DateTime();

        $last->modify("-5 min");

        $whosOnline = $this->em->createQuery("
                DELETE WhosOnlineBundle:WhosOnline w 
                WHERE w.lastActivity < :lastActivity
            ")->execute(array('lastActivity' => $last));

        return TRUE;
    }

    public function getActiveUsers()
    {

        $last = new \DateTime();

        $last->modify("-5 min");


        return $this->em->createQuery("
                SELECT w WhosOnlineBundle:WhosOnline w 
                WHERE w.lastActivity >= :lastActivity
                        ")
                        ->setParameter('lastActivity', $last)
                        ->getResult();
    }

    public function getOnlineUsers()
    {

        $last = new \DateTime();

        $last->modify("-5 min");


        return $this->em->createQuery("
                SELECT w WhosOnlineBundle:WhosOnline w 
                WHERE w.lastActivity >= :lastActivity
                        ")
                        ->setParameter('lastActivity', $last)
                        ->getResult();
    }

}