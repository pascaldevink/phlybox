<?php

require_once(dirname(__FILE__) . '/../vendor/autoload.php');

use pascaldevink\Phlybox\Controller\WebhookController;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

$routes = new RouteCollection();
$routes->add('hello', new Route('/incoming/up', array(
        '_controller' => array(new WebhookController(), 'upAction'),
    )
));

$request = Request::createFromGlobals();

$matcher = new UrlMatcher($routes, new RequestContext());

$dispatcher = new EventDispatcher();
$dispatcher->addSubscriber(new RouterListener($matcher));

$resolver = new ControllerResolver();

$kernel = new HttpKernel($dispatcher, $resolver);

$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);