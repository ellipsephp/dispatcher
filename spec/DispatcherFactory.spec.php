<?php

use function Eloquent\Phony\Kahlan\mock;

use Interop\Http\Server\RequestHandlerInterface;

use Ellipse\Dispatcher;
use Ellipse\DispatcherFactoryInterface;
use Ellipse\DispatcherFactory;
use Ellipse\Dispatcher\Exceptions\RequestHandlerTypeException;

describe('DispatcherFactory', function () {

    beforeEach(function () {

        $this->factory = new DispatcherFactory;

    });

    it('should implement DispatcherFactoryInterface', function () {

        expect($this->factory)->toBeAnInstanceOf(DispatcherFactoryInterface::class);

    });

    describe('->__invoke()', function () {

        context('when the given request handler implements RequestHandlerInterface', function () {

            beforeEach(function () {

                $this->handler = mock(RequestHandlerInterface::class)->get();

            });

            context('when no iterable list of middleware is given', function () {

                it('should return a new Dispatcher with the given request handler and an empty array of middleware', function () {

                    $test = ($this->factory)($this->handler);

                    $dispatcher = new Dispatcher([], $this->handler);

                    expect($test)->toEqual($dispatcher);

                });

            });

            context('when an iterable list of middleware is given', function () {

                it('should return a new Dispatcher using the given request handler and iterable list of middleware', function () {

                    $test = function ($middleware) {

                        $test = ($this->factory)($this->handler, $middleware);

                        $dispatcher = new Dispatcher($middleware, $this->handler);

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

        context('when the given request handler implements RequestHandlerInterface', function () {

            it('should throw a RequestHandlerTypeException', function () {

                $test = function () {

                    ($this->factory)('handler');

                };

                $exception = new RequestHandlerTypeException('handler');

                expect($test)->toThrow($exception);

            });

        });

    });

});
