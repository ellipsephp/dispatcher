<?php

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;

use Ellipse\Dispatcher\Dispatcher;
use Ellipse\Dispatcher\Exceptions\ElementIsNotAMiddlewareException;
use Ellipse\Dispatcher\Exceptions\InvalidMiddlewareReturnValueException;

describe('Dispatcher', function () {

    beforeEach(function () {

        $this->handler = Mockery::mock(RequestHandlerInterface::class);

    });

    afterEach(function () {

        Mockery::close();

    });

    it('should implements DispatcherInterface', function () {

        expect(new Dispatcher([], $this->handler))->to->be->an->instanceof(RequestHandlerInterface::class);

    });

    describe('::create()', function () {

        it('should return a Dispatcher instance', function () {

            $test = Dispatcher::create([], $this->handler);

            expect($test)->to->be->an->instanceof(Dispatcher::class);

        });

    });

    describe('->process()', function () {

        beforeEach(function () {

            $this->request = Mockery::mock(ServerRequestInterface::class);
            $this->response = Mockery::mock(ResponseInterface::class);

        });

        it('should return the response produced by a list of middleware when it return a response', function () {

            $middleware1 = new class implements MiddlewareInterface
            {
                public function process(ServerRequestInterface $request, RequestHandlerInterface $delegate)
                {
                    return $delegate->handle($request);
                }
            };

            $middleware2 = Mockery::mock(MiddlewareInterface::class);

            $dispatcher = new Dispatcher([$middleware1, $middleware2], $this->handler);

            $middleware2->shouldReceive('process')->once()
                ->with($this->request, $this->handler)
                ->andReturn($this->response);

            $this->handler->shouldNotReceive('process');

            $test = $dispatcher->handle($this->request);

            expect($test)->to->be->equal($this->response);

        });

        it('should return the response produced by the final delegate when no middleware returned a response', function () {

            $middleware = new class implements MiddlewareInterface
            {
                public function process(ServerRequestInterface $request, RequestHandlerInterface $delegate)
                {
                    return $delegate->handle($request);
                }
            };

            $this->handler->shouldReceive('handle')->once()
                ->with($this->request)
                ->andReturn($this->response);

            $dispatcher = new Dispatcher([$middleware], $this->handler);

            $test = $dispatcher->handle($this->request);

            expect($test)->to->be->equal($this->response);

        });

        it('should fail when an element is not a middleware', function () {

            $dispatcher = new Dispatcher(['middleware'], $this->handler);

            expect([$dispatcher, 'handle'])->with($this->request)
                ->to->throw(ElementIsNotAMiddlewareException::class);

        });

        it('should fail when a middleware does not return an instance of ResponseInterface', function () {

            $middleware = Mockery::mock(MiddlewareInterface::class);

            $middleware->shouldReceive('process')->once()
                ->with($this->request, Mockery::type(RequestHandlerInterface::class))
                ->andReturn('test');

            $dispatcher = new Dispatcher([$middleware], $this->handler);

            expect([$dispatcher, 'handle'])->with($this->request)
                ->to->throw(InvalidMiddlewareReturnValueException::class);

        });

    });

});
