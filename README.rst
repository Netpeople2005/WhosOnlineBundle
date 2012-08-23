WhosOnlineBundle
================

Bundle que ofrece funcionalidades para gestionar y mantener información de
los usuarios conectados en una aplicación.

Instalación
-----------

Descargar el repositorio y colocarlo en:

::

    ProyectoSymfony/vendors/bundles/Netpeople/WhosOnlineBundle

Agregar el Espacio de Nombres al autoloader

::

    <?php

    // app/autoload.php
    $loader->registerNamespaces(array(
        // ...
        'Netpeople'         => __DIR__.'/../vendor/bundles',
        // ...
    ));

Registrar el Bundle en el AppKernel

::

    <?php

    // AppKernel::registerBundles()
    $bundles = array(
        // ...
        new Netpeople\WhosOnlineBundle\WhosOnlineBundle(),
        // ...
    );

Opcional: Agregar la configuración del bundle en el config.ini
( este paso es opcional )

::

    #app/config/config.yml 
    whos_online:

        #cada parametro debe tener un valor de formatos de fecha y hora
        #relativos de php, pero SIN SIGNO
        # http://www.php.net/manual/es/datetime.formats.relative.php

        inactive_in: 10 min #por defecto 5 min

        offline_in: 6 hours #por defecto 30 min

        clear_in: 10 days #por defecto 2 days

        #ningun parametro es obligatorio, todos son opcionales debido a que tiene
        #valores por defecto.

Adicional
---------

Agregar esto en el routing_dev.yml para ver los usuarios conectados:

::

    #app/config/routing_dev.yml
    _whos_online:
        resource: "@WhosOnlineBundle/Resources/config/routing.yml"
        prefix:   /whos_online
    
