Teknoo Software - ReactPHP Symfony Bridge
=========================================

Installation & Requirements
---------------------------
To install this library with composer, run this command :

    composer require react/http:dev-master
    composer require teknoo/reactphp-symfony

This library requires :

    * PHP 7+
    * Composer
    * Symfony 3.2+
    * ReactPHP 0.6+

Execution
---------

Via the Symfony Console :

    #Env prod
    bin/console reactphp:run -i 0.0.0.0 -p 8080

    #End dev
    bin/console reactphp:run -i 0.0.0.0 -p 8080 -e dev

Via a PHP file :

    #!/usr/bin/env php
    <?php

    use React\EventLoop\Factory;
    use React\Socket\Server as SocketServer;
    use React\Http\Server as HttpServer;
    use Teknoo\ReactPHPBundle\Bridge\RequestBridge;
    use Teknoo\ReactPHPBundle\Bridge\RequestListener;

    require __DIR__.'/../app/autoload.php';
    if (\file_exists(__DIR__.'/../var/bootstrap.php.cache')) {
        include_once __DIR__ . '/../var/bootstrap.php.cache';
    }

    $kernel = new AppKernel('prod', false);
    $kernel->loadClassCache();

    $requestBridge = new RequestBridge($kernel);
    $requestListener = new RequestListener($requestBridge);

    $loop = Factory::create();
    $socket = new SocketServer('0.0.0.0:8080', $loop);
    $http = new HttpServer($socket);

    $http->on('request', $requestListener);
    $loop->run();

Credits
-------
Richard Déloge - <richarddeloge@gmail.com> - Lead developer.
Teknoo Software - <http://teknoo.software>

About Teknoo Software
---------------------
**Teknoo Software** is a PHP software editor, founded by Richard Déloge. 
Teknoo Software's DNA is simple : Provide to our partners and to the community a set of high quality services or software,
 sharing knowledge and skills.

License
-------
ReactPHP Symfony Bridge is licensed under the MIT License - see the licenses folder for details

Contribute :)
-------------

You are welcome to contribute to this project. [Fork it on Github](CONTRIBUTING.md)
