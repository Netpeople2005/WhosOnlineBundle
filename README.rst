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

        #cada parametro debe tener un valor de formatos de fecha y hora relativos de php, pero SIN SIGNO<

        #este parametro indica el tiempo maximo para considerar a un usuario
        #activo en el sistema, es decir, si la ultima actividad de un usuario
        #logueado es menor a este tiempo se considera activo (por defecto 5 minutos).
        inactive_in: 10 min

        #este parametro indica el tiempo maximo para considerar a un usuario
        #online en el sistema, es decir, si la ultima actividad de un usuario
        #logueado es menor a este tiempo se considera online (por defecto 30 minutos).
        offline_in: 6 hours

        #este parametro indica cada cuanto tiempo deben borrarse datos antiguos
        #de la tabla de los WhosOnline (por defecto 2 dias).
        clear_in: 10 days

        #el siguiente parametro indica si los usuarios identificados anonimamente en el sistema
        #tambien deben ser registrados en el WhosOnline (por defecto no lo hace).
        register_anonymous: false

        #ningun parametro es obligatorio, todos son opcionales debido a que tienen
        #valores por defecto.

Los valores de los parametros son formatos de fecha y hora relativos de php
http://www.php.net/manual/es/datetime.formats.relative.php


Adicional
---------

Agregar esto en el routing_dev.yml para ver los usuarios conectados:

::

    #app/config/routing_dev.yml
    _whos_online:
        resource: "@WhosOnlineBundle/Resources/config/routing.yml"
        prefix:   /whos_online
    
