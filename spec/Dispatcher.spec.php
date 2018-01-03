<?php

use function Eloquent\Phony\Kahlan\mock;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;

use Ellipse\Dispatcher;

describe('Dispatcher', function () {

    beforeEach(function () {

        $this->handler = mock(RequestHandlerInterface::class);

    });

    it('should implement RequestHandlerInterface', function () {

        $test = new Dispatcher([], $this->handler->get());

        expect($test)->toBeAnInstanceOf(RequestHandlerInterface::class);

    });

    describe('->handle()', function () {

        beforeEach(function () {

            $this->request = mock(ServerRequestInterface::class)->get();
            $this->response = mock(ResponseInterface::class)->get();

        });

        context('when the given iterable list of middleware is not empty', function () {

            beforeEach(function () {

                $this->middleware1 = mock(MiddlewareInterface::class);
                $this->middleware2 = mock(MiddlewareInterface::class);

            });

            it('should proxy the first middleware ->process() method with the given request', function () {

                $test = function ($middleware) {

                    $dispatcher = new Dispatcher($middleware, $this->handler->get());

                    $this->middleware1->process->with($this->request, '~')->returns($this->response);

                    $test = $dispatcher->handle($this->request);

                    expect($test)->toBe($this->response);

                };

                $middleware = [
                    $this->middleware1->get(),
                    $this->middleware2->get(),
                ];

                $test($middleware);
                $test(new ArrayIterator($middleware));
                $test(new class ($middleware) implements IteratorAggregate
                {
                    public function __construct($middleware) { $this->middleware = $middleware; }
                    public function getIterator() { return new ArrayIterator($this->middleware); }
                });

            });

            it('should proxy the first middleware ->process() method with a new Dispatcher dispatching the remaining middleware', function () {

                $test = function ($middleware1, $middleware2) {

                    $dispatcher1 = new Dispatcher($middleware1, $this->handler->get());
                    $dispatcher2 = new Dispatcher($middleware2, $this->handler->get());

                    $this->middleware1->process->with('~', $dispatcher2)->returns($this->response);

                    $test = $dispatcher1->handle($this->request);

                    expect($test)->toBe($this->response);

                };

                $middleware1 = [
                    $this->middleware1->get(),
                    $this->middleware2->get(),
                ];

                $middleware2 = [
                    $this->middleware2->get(),
                ];

                $test($middleware1, $middleware2);
                $test(new ArrayIterator($middleware1), $middleware2);
                $test(new class ($middleware1) implements IteratorAggregate
                {
                    public function __construct($middleware1) { $this->middleware1 = $middleware1; }
                    public function getIterator() { return new ArrayIterator($this->middleware1); }
                }, $middleware2);

            });

        });

        context('when the given iterable list of middleware is empty', function () {

            it('should proxy the handler ->handle() method', function () {

                $test = function ($empty) {

                    $dispatcher = new Dispatcher($empty, $this->handler->get());

                    $this->handler->handle->with($this->request)->returns($this->response);

                    $test = $dispatcher->handle($this->request);

                    expect($test)->toBe($this->response);

                };

                $test([]);
                $test(new ArrayIterator([]));
                $test(new class implements IteratorAggregate
                {
                    public function getIterator() { return new ArrayIterator([]); }
                });

            });

        });

    });

});
