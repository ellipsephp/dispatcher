<?php

use function Eloquent\Phony\Kahlan\mock;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use Ellipse\Dispatcher;
use Ellipse\DispatcherFactoryInterface;
use Ellipse\DispatcherFactory;
use Ellipse\Dispatcher\Exceptions\DispatcherCreationException;

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

            context('when no iterable middleware queue is given', function () {

                it('should return a new Dispatcher with the given request handler and an empty array of middleware', function () {

                    $test = ($this->factory)($this->handler);

                    $dispatcher = new Dispatcher($this->handler, []);

                    expect($test)->toEqual($dispatcher);

                });

            });

            context('when an iterable middleware queue is given', function () {

                context('when all the middleware in the given middleware queue implements MiddlewareInterface', function () {

                    it('should return a new Dispatcher using the given request handler iterable middleware queue', function () {

                        $test = function ($middleware) {

                            $test = ($this->factory)($this->handler, $middleware);

                            $dispatcher = new Dispatcher($this->handler, $middleware);

                            expect($test)->toEqual($dispatcher);

                        };

                        $middleware = [
                            mock(MiddlewareInterface::class)->get(),
                            mock(MiddlewareInterface::class)->get(),
                        ];

                        $test($middleware);
                        $test(new ArrayIterator($middleware));
                        $test(new class ($middleware) implements IteratorAggregate
                        {
                            public function __construct($middleware) { $this->middleware = $middleware; }
                            public function getIterator() { return new ArrayIterator($this->middleware); }
                        });

                    });

                });

                context('when a middleware in the given middleware queue does not implement MiddlewareInterface', function () {

                    it('should throw a DispatcherCreationException', function () {

                        $test = function () {

                            ($this->factory)($this->handler, [
                                mock(MiddlewareInterface::class)->get(),
                                'middleware',
                            ]);

                        };

                        $exception = new DispatcherCreationException(mock(TypeError::class)->get());

                        expect($test)->toThrow($exception);

                    });

                });

            });

        });

        context('when the given request handler does not implement RequestHandlerInterface', function () {

            it('should throw a DispatcherCreationException', function () {

                $test = function () {

                    ($this->factory)('handler');

                };

                $exception = new DispatcherCreationException(mock(TypeError::class)->get());

                expect($test)->toThrow($exception);

            });

        });

    });

});
