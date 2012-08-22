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

    protected $inactiveIn;
    
    protected $offlineIn;
    
    protected $clearIn;

    /**
     * @var \Doctrine\ORM\EntityManager 
     */
    private $em;

    public function __construct(Registry $em, $inactiveIn, $offlineIn, $clearIn)
    {
        $this->em = $em->getEntityManager();
        $this->inactiveIn = "-$inactiveIn";
        $this->offlineIn = "-$offlineIn";
        $this->clearIn = "+$clearIn";
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
        $offlineIn = new \DateTime($this->offlineIn);

        $whosOnline = $this->em->createQuery("
                DELETE WhosOnlineBundle:WhosOnline w 
                WHERE w.lastActivity < :offlineIn
            ")->execute(array('offlineIn' => $offlineIn));

        return TRUE;
    }

    public function clear()
    {
        $clearIn = new \DateTime($this->clearIn);

        $whosOnline = $this->em->createQuery("
                DELETE WhosOnlineBundle:WhosOnline w 
                WHERE w.lastActivity < :clearIn
            ")->execute(array('clearIn' => $clearIn));

        return TRUE;
    }

    public function getActiveUsers()
    {

        $inactiveIn = new \DateTime($this->inactiveIn);

        return $this->em->createQuery("
                SELECT w FROM WhosOnlineBundle:WhosOnline w 
                WHERE w.lastActivity >= :inactiveIn
                        ")
                        ->setParameter('inactiveIn', $inactiveIn)
                        ->getResult();
    }

    public function getOnlineUsers()
    {

        $offlineIn = new \DateTime($this->offlineIn);

        return $this->em->createQuery("
                SELECT w FROM WhosOnlineBundle:WhosOnline w 
                WHERE w.lastActivity >= :offlineIn
                        ")
                        ->setParameter('offlineIn', $offlineIn)
                        ->getResult();
    }

    public function isClean()
    {
        $fileName = __DIR__ . '/../Files/last_clean.txt';

        if ('' == ($content = file_get_contents($fileName))) {
            //si no tenia una fecha creada, la creamos
            $date = new \DateTime();
            file_put_contents($fileName, $date->format(\DateTime::W3C));
            return TRUE;
        } else {
            //obtengo la fecha de la ultima limpieza.
            $lastClean = new \DateTime($content);
            //le sumo el tiempo que debe haber entre cada limpieza
            //y obtengo la diferencia con la fecha de hoy.
            $interval = $lastClean->modify($this->clearIn)->diff(new \DateTime());
            //si la diferencia de la ultima limpieza mas el tiempo 
            //entre limpiezas es mayor a la fecha actual,
            //se debe hacer limpieza.
            return $interval->format('%r%d') <= 0;
        }
    }

}