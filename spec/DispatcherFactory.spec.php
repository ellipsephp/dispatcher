<?php

use Ellipse\Dispatcher;
use Ellipse\DispatcherFactoryInterface;
use Ellipse\DispatcherFactory;
use Ellipse\Dispatcher\MiddlewareProxy;
use Ellipse\Dispatcher\RequestHandlerProxy;

describe('DispatcherFactory', function () {

    beforeEach(function () {

        $this->factory = new DispatcherFactory;

    });

    it('should implement DispatcherFactoryInterface', function () {

        expect($this->factory)->toBeAnInstanceOf(DispatcherFactoryInterface::class);

    });

    describe('->__invoke()', function () {

        context('when no iterable list of middleware is given', function () {

            it('should return a new Dispatcher with the given request handler and an empty array of middleware wrapped in proxies', function () {

                $test = ($this->factory)('handler');

                $dispatcher = new Dispatcher(
                    new MiddlewareProxy([]),
                    new RequestHandlerProxy('handler')
                );

                expect($test)->toEqual($dispatcher);

            });

        });

        context('when an iterable list of middleware is given', function () {

            it('should return a new Dispatcher using the given request handler and iterable list of middleware wrapped in proxies', function () {

                $test = function ($middleware) {

                    $test = ($this->factory)('handler', $middleware);

                    $dispatcher = new Dispatcher(
                        new MiddlewareProxy($middleware),
                        new RequestHandlerProxy('handler')
                    );

                    expect($test)->toEqual($dispatcher);

                };

                $middleware = ['middleware1', 'middleware2'];

                $test($middleware);
                $test(new ArrayIterator($middleware));
                $test(new class ($middleware) implements IteratorAggregate
                {
                    public function __construct($middleware) { $this->middleware = $middleware; }
                    public function getIterator() { return new ArrayIterator($this->middleware); }
                });

            });

        });

    });

});
