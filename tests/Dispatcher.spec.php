<?php

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;

use Ellipse\Dispatcher\Dispatcher;
use Ellipse\Dispatcher\Exceptions\NoResponseReturnedException;
use Ellipse\Dispatcher\Exceptions\InvalidMiddlewareReturnValueException;

describe('Dispatcher', function () {

    afterEach(function () {

        Mockery::close();

    });

    it('should implements DispatcherInterface', function () {

        expect(new Dispatcher)->to->be->an->instanceof(DelegateInterface::class);

    });

    describe('::create()', function () {

        it('should return a Dispatcher instance', function () {

            $test = Dispatcher::create([], Mockery::mock(DelegateInterface::class));

            expect($test)->to->be->an->instanceof(Dispatcher::class);

        });

    });

    describe('->process()', function () {

        beforeEach(function () {

            $this->request = Mockery::mock(ServerRequestInterface::class);
            $this->response = Mockery::mock(ResponseInterface::class);
            $this->final = Mockery::mock(DelegateInterface::class);

        });

        it('should return the response produced by an array of middleware', function () {

            $resolver = Mockery::mock(ResolverInterface::class);

            $middleware1 = new class implements MiddlewareInterface {

                public function process(ServerRequestInterface $request, DelegateInterface $delegate)
                {
                    return $delegate->process($request);
                }
            };

            $middleware2 = Mockery::mock(MiddlewareInterface::class);

            $dispatcher = new Dispatcher([$middleware1, $middleware2], $this->final, $resolver);

            $middleware2->shouldReceive('process')->once()
                ->with($this->request, $this->final)
                ->andReturn($this->response);

            $this->final->shouldNotReceive('process');

            $test = $dispatcher->process($this->request);

            expect($test)->to->be->equal($this->response);

        });

        it('should return the response produced by a traversable instance containing middleware', function () {

            $resolver = Mockery::mock(ResolverInterface::class);

            $middleware1 = new class implements MiddlewareInterface {

                public function process(ServerRequestInterface $request, DelegateInterface $delegate)
                {
                    return $delegate->process($request);
                }
            };

            $middleware2 = Mockery::mock(MiddlewareInterface::class);

            $dispatcher = new Dispatcher(new ArrayObject([$middleware1, $middleware2]), $this->final, $resolver);

            $this->final->shouldNotReceive('process');

            $middleware2->shouldReceive('process')->once()
                ->with($this->request, $this->final)
                ->andReturn($this->response);

            $test = $dispatcher->process($this->request);

            expect($test)->to->be->equal($this->response);

        });

        it('should return the response produced by the final delegate when no middleware returned a response', function () {

            $middleware = new class implements MiddlewareInterface {

                public function process(ServerRequestInterface $request, DelegateInterface $delegate)
                {
                    return $delegate->process($request);
                }
            };

            $this->final->shouldReceive('process')->once()
                ->with($this->request)
                ->andReturn($this->response);

            $dispatcher = new Dispatcher([$middleware], $this->final);

            $test = $dispatcher->process($this->request);

            expect($test)->to->be->equal($this->response);

        });

        it('should fail when a middleware does not return an instance of ResponseInterface', function () {

            $middleware = Mockery::mock(MiddlewareInterface::class);

            $middleware->shouldReceive('process')->once()
                ->with($this->request, Mockery::type(DelegateInterface::class))
                ->andReturn('test');

            $dispatcher = new Dispatcher([$middleware]);

            expect([$dispatcher, 'process'])->with($this->request)
                ->to->throw(InvalidMiddlewareReturnValueException::class);

        });

        it('should fail when no response is returned', function () {

            $dispatcher = new Dispatcher;

            expect([$dispatcher, 'process'])->with($this->request)
                ->to->throw(NoResponseReturnedException::class);

        });

    });

});
