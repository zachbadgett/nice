<?php

namespace TylerSommer\Nice\Tests\Router;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use TylerSommer\Nice\Router\RouterSubscriber;

class RouterSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test a Found route
     */
    public function testFound()
    {
        $subscriber = $this->getSubscriber();
        $request = Request::create('/', 'GET');
        
        $event = $this->getEvent($request);
        
        $subscriber->onKernelRequest($event);
        
        $this->assertEquals('handler1', $request->get('_controller'));
    }

    /**
     * Test a Found route with parameters
     */
    public function testFoundWithParams()
    {
        $subscriber = $this->getSubscriber();
        $request = Request::create('/hello/test', 'GET');

        $event = $this->getEvent($request);

        $subscriber->onKernelRequest($event);

        $this->assertEquals('handler2', $request->get('_controller'));
        $this->assertEquals('test', $request->get('value'));
    }

    /**
     * Test a match, but Method Not Allowed route
     */
    public function testMethodNotAllowed()
    {
        $subscriber = $this->getSubscriber();
        $request = Request::create('/', 'POST');

        $event = $this->getEvent($request);

        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException');
        
        $subscriber->onKernelRequest($event);
    }

    /**
     * Test not found
     */
    public function testNotFound()
    {
        $subscriber = $this->getSubscriber();
        $request = Request::create('/not-real', 'GET');

        $event = $this->getEvent($request);

        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');

        $subscriber->onKernelRequest($event);
    }

    /**
     * @return RouterSubscriber
     */
    private function getSubscriber()
    {
        $routeDispatcher = \FastRoute\simpleDispatcher(function(\FastRoute\RouteCollector $r) {
                $r->addRoute('GET', '/', 'handler1');
                $r->addRoute('GET', '/hello/{value}', 'handler2');
            });
        
        $subscriber = new RouterSubscriber($routeDispatcher);
        
        return $subscriber;
    }

    /**
     * @param Request $request
     *
     * @return GetResponseEvent
     */
    private function getEvent(Request $request)
    {
        return new GetResponseEvent(
            $this->getMockForAbstractClass('Symfony\Component\HttpKernel\HttpKernelInterface'), 
            $request, 
            HttpKernelInterface::MASTER_REQUEST
        );
    }
}