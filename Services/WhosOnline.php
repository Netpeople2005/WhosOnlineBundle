<?php

namespace Netpeople\WhosOnlineBundle\Services;

use Symfony\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Netpeople\WhosOnlineBundle\Entity\WhosOnline as MyEntity;

/**
 * Servicio que gestiona y mantiene registros de los 
 * usuarios en linea en la aplicacíon.
 *
 * @author maguirre
 */
class WhosOnline
{

    /**
     * Indica el tiempo limite para considerar a un usuario como activo
     * en el sistema.
     * 
     * ejemplo:
     * 
     * si inactiveIn es igual a +5 min significa que los usuarios
     * con un lastActivity menor a ese tiempo están activos en el sistema
     * y los que tengan un tiempo mayor, son considerados inactivos
     *
     * @var string 
     */
    protected $inactiveIn;

    /**
     * Indica el tiempo limite para considerar a un usuario como online en el
     * sistema.
     *
     * @var string 
     */
    protected $offlineIn;

    /**
     * Indica cada cuanto tiempo debe limpiarse la tabla del whos_online
     * Para borrar usuarios logueados con IP que han cambiado, etc.
     * 
     * @var string 
     */
    protected $clearIn;

    /**
     * @var \Doctrine\ORM\EntityManager 
     */
    protected $em;

    /**
     * Dirección del archivo donde se encuentra la fecha y hora de la
     * ultima limpieza de la tabla WhosOnline.
     * 
     * @var string 
     */
    private $fileNameLastClean;

    /**
     * Constructor de la Clase
     * @param Registry $em objeto doctrine
     * @param string $inactiveIn
     * @param string $offlineIn
     * @param string $clearIn 
     */
    public function __construct(Registry $em, $inactiveIn, $offlineIn, $clearIn)
    {
        $this->em = $em->getEntityManager();
        $this->inactiveIn = "-$inactiveIn";
        $this->offlineIn = "-$offlineIn";
        $this->clearIn = "+$clearIn";
        $this->fileNameLastClean = dirname(__DIR__) . '/Files/last_clean.txt';
    }

    /**
     * Registra a un usuario en el WhosOnline si no existia.
     * si ya existia actualiza su actividad.
     * 
     * @param TokenInterface $token
     * @param stirng $ip IP desde donde está conectado el usuario.
     * @return boolean 
     */
    public function registerOnline(TokenInterface $token, $ip)
    {

        //verifico si ya existe en la tabla
        if ($this->isOnline($token, $ip)) {
            //si existe lo que hago es actualizar :-)
            return $this->registerActivity($token, $ip);
        } else {
            //si no existe creo el registro
            $whosOnline = new MyEntity($token->getUsername(), $ip);

            $this->em->persist($whosOnline);
            $this->em->flush();

            return TRUE;
        }
    }

    /**
     * Devuelve TRUE si el usuario está registrado en el WhosOnline
     * @param TokenInterface $token
     * @param type $ip IP desde donde está conectado el usuario.
     * @return type 
     */
    public function isOnline(TokenInterface $token, $ip)
    {
        $offlineIn = new \DateTime($this->offlineIn);

        $res = $this->em->createQueryBuilder()
                ->select('COUNT(w)')
                ->from('WhosOnlineBundle:WhosOnline', 'w')
                ->where('w.username = :username 
                    AND w.ip = :ip
                    AND w.lastActivity >= :offlineIn')
                ->setParameter('username', $token->getUsername())
                ->setParameter('ip', $ip)
                ->setParameter('offlineIn', $offlineIn)
                ->getQuery()
                ->getSingleScalarResult();

        return $res > 0;
    }

    /**
     * Actualiza el lastActivity del usuario para mantenerlo activo
     * @param TokenInterface $token
     * @param type $ip IP desde donde está conectado el usuario.
     * @return boolean 
     */
    public function registerActivity(TokenInterface $token, $ip)
    {
        //actualizo el atributo lastActivity del registro 
        //que representa al usuario logueado.
        $this->em->createQueryBuilder()
                ->update('WhosOnlineBundle:WhosOnline', 'w')
                ->set('w.lastActivity', ':now')
                ->where('w.username = :username AND w.ip = :ip')
                ->setParameter('now', new \DateTime())
                ->setParameter('username', $token->getUsername())
                ->setParameter('ip', $ip)
                ->getQuery()
                ->execute();
        return TRUE;
    }

    /**
     * Elimina a un Usuario del WhosOnline
     * @param TokenInterface $token
     * @param type $ip IP desde donde está conectado el usuario.
     * @return boolean 
     */
    public function delete(TokenInterface $token, $ip)
    {
        $this->em->createQueryBuilder()
                ->delete('WhosOnlineBundle:WhosOnline', 'w')
                ->where('w.username = :username AND w.ip = :ip')
                ->setParameter('username', $token->getUsername())
                ->setParameter('ip', $ip)
                ->getQuery()
                ->execute();

        return TRUE;
    }

    /**
     * Elimina a los usuarios considerados Offline del WhosOnline.
     * @return boolean 
     */
    public function deleteOffline()
    {
        $offlineIn = new \DateTime($this->offlineIn);

        $this->em->createQueryBuilder()
                ->delete('WhosOnlineBundle:WhosOnline', 'w')
                ->where('w.lastActivity < :offlineIn')
                ->setParameter('offlineIn', $offlineIn)
                ->getQuery()
                ->execute();

        return TRUE;
    }

    /**
     * Limpia Registros viejos de la tabla, cualquier usuario
     * deslogueado con el lastActivity menor al tiempo entre cada
     * limpieza.
     * @return boolean 
     */
    public function clear()
    {
        $clearIn = new \DateTime($this->clearIn);

        $this->em->createQueryBuilder()
                ->delete('WhosOnlineBundle:WhosOnline', 'w')
                ->where('w.lastActivity < :clearIn')
                ->setParameter('clearIn', $clearIn)
                ->getQuery()
                ->execute();

        //actualizo el archivo a la fecha y hora actual.
        $this->updateLastClean();

        return TRUE;
    }

    /**
     * Devuelve los registros en la tabla WhosOnline de los Usuarios
     * considerados activos en el sistema.
     * @return array 
     */
    public function getActiveUsers()
    {
        $inactiveIn = new \DateTime($this->inactiveIn);

        return $this->em->createQueryBuilder()
                        ->select('w')
                        ->from('WhosOnlineBundle:WhosOnline', 'w')
                        ->where('w.lastActivity >= :inactiveIn')
                        ->setParameter('inactiveIn', $inactiveIn)
                        ->getQuery()
                        ->getResult();
    }

    /**
     * Devuelve los registros en la tabla WhosOnline de los Usuarios
     * considerados online en el sistema.
     * @return array 
     */
    public function getOnlineUsers()
    {
        $offlineIn = new \DateTime($this->offlineIn);
        $inactiveIn = new \DateTime($this->inactiveIn);

        $res = $this->em->createQueryBuilder()
                ->select('w')
                ->from('WhosOnlineBundle:WhosOnline', 'w')
                ->where('w.lastActivity >= :offlineIn')
                ->setParameter('offlineIn', $offlineIn)
                ->getQuery()
                ->getResult();
        foreach ((array) $res as $e) {
            if ($e->getLastActivity() < $inactiveIn) {
                $e->setActive(FALSE);
            }
        }
        return $res;
    }

    /**
     * Devuelve TRUE si la tabla de los WhosOnline es considerada limpia.
     * 
     * Esta consideración se basa en la fecha de la ultima limpieza,
     * la cual está almacenada en un archivo de texto.
     * Si la diferencia de la fecha actual y la fecha de la ultima limpieza es
     * mayor al tiempo establecido entre limpiezas, esta función devolverá FALSE
     * 
     * @return boolean 
     */
    public function isClean()
    {
        $file = dirname($this->fileNameLastClean);
        if (!is_dir($file)) {
            throw new \LogicException("No existe el Directorio <b>$file</b> Necesario para contener el archivo de texto con la información de la ultima limpieza de la tabla whos_online");
        }

        if (!file_exists($this->fileNameLastClean) ||
                !$content = file_get_contents($this->fileNameLastClean)) {
            //si no tenia una fecha creada, la creamos
            $this->updateLastClean();
            return TRUE;
        } else {
            //obtengo la fecha de la ultima limpieza.
            $lastClean = new \DateTime($content);
            //obtengo la fecha y hora actual
            $now = new \DateTime('now');

            //le sumo el tiempo que debe haber entre cada limpieza
            $lastClean->modify($this->clearIn);
            //si la fecha actual es menor a la fecha de la ultima
            //limpieza mas el tiempo entre limpiezas,
            //asumimos que la bd esta limpia aun.
            return $now < $lastClean;
        }
    }

    /**
     * Actualiza el archivo con la info de la ultima actualización
     * a la fecha y hora actual
     * @param \DateTime $date 
     */
    protected function updateLastClean()
    {
        $file = dirname($this->fileNameLastClean);

        if (!is_writable($file)) {
            throw new \LogicException("No se puede escribir en el Directorio <b>$file</b> Es Necesario poder escribir en el archivo de texto para actualizar la información de la ultima limpieza de la tabla whos_online");
        }

        $date = new \DateTime();
        file_put_contents($this->fileNameLastClean, $date->format(\DateTime::W3C));
    }

}