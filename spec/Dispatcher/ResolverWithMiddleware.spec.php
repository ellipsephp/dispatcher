<?php

use function Eloquent\Phony\Kahlan\mock;
use function Eloquent\Phony\Kahlan\partialMock;

use Interop\Http\Server\RequestHandlerInterface;

use Ellipse\Dispatcher;
use Ellipse\DispatcherFactoryInterface;
use Ellipse\Dispatcher\ResolverWithMiddleware;

describe('ResolverWithMiddleware', function () {

    beforeEach(function () {

        $this->delegate = mock(DispatcherFactoryInterface::class);

        $this->middleware = ['middleware1', 'middleware2'];

        $this->resolver = new ResolverWithMiddleware($this->delegate->get(), $this->middleware);

    });

    it('should implement DispatcherFactoryInterface', function () {

        expect($this->resolver)->toBeAnInstanceOf(DispatcherFactoryInterface::class);

    });

    describe('->with()', function () {

        it ('should return a new ResolverWithMiddleware using this resolver and the given iterable middleware queue', function () {

            $test = function ($middleware) {

                $test = $this->resolver->with($middleware);

                $resolver = new ResolverWithMiddleware($this->resolver, $middleware);

                expect($test)->toEqual($resolver);

            };

            $middleware = ['middleware3', 'middleware4'];

            $test($middleware);
            $test(new ArrayIterator($middleware));
            $test(new class ($middleware) implements IteratorAggregate
            {
                public function __construct($middleware) { $this->middleware = $middleware; }
                public function getIterator() { return new ArrayIterator($this->middleware); }
            });

        });

    });

    describe('->dispatcher()', function () {

        beforeEach(function () {

            $this->dispatcher = mock(Dispatcher::class)->get();

        });

        context("when the delegate is a ResolverWithMiddleware", function () {

            it('should proxy the delegate ->dispatcher() method', function () {

                $test = function ($middleware) {

                    $delegate = mock(ResolverWithMiddleware::class);

                    $resolver = new ResolverWithMiddleware($delegate->get(), []);

                    $delegate->dispatcher->with('handler', $middleware)->returns($this->dispatcher);

                    $test = $resolver->dispatcher('handler', $middleware);

                    expect($test)->toBe($this->dispatcher);

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

        context("when the delegate is not a ResolverWithMiddleware", function () {

            it('should proxy the delegate', function () {

                $test = function ($middleware) {

                    $delegate = mock(DispatcherFactoryInterface::class);

                    $resolver = new ResolverWithMiddleware($delegate->get(), []);

                    $delegate->__invoke->with('handler', $middleware)->returns($this->dispatcher);

                    $test = $resolver->dispatcher('handler', $middleware);

                    expect($test)->toBe($this->dispatcher);

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

    describe('->__invoke()', function () {

        beforeEach(function () {

            $this->delegate = mock(DispatcherFactoryInterface::class);
            $this->middleware = ['middleware3', 'middleware4'];

            $this->resolver = partialMock(ResolverWithMiddleware::class, [
                $this->delegate->get(),
                $this->middleware,
            ]);

            $this->handler = mock(RequestHandlerInterface::class)->get();
            $this->dispatcher1 = mock(Dispatcher::class)->get();
            $this->dispatcher2 = mock(Dispatcher::class)->get();

        });

        context('when no iterable middleware queue is given', function () {

            it('should resolve the decorated dispatcher with an empty array of middleware', function () {

                $this->resolver->dispatcher->with($this->handler, [])->returns($this->dispatcher1);

                $this->delegate->__invoke->with($this->dispatcher1, $this->middleware)->returns($this->dispatcher2);

                $test = ($this->resolver->get())($this->handler);

                expect($test)->toBe($this->dispatcher2);

            });

        });

        context('when an iterable middleware queue is given', function () {

            it('should resolve the decorated dispatcher with the given iterable middleware queue', function () {

                $test = function ($middleware) {

                    $this->resolver->dispatcher->with($this->handler, $middleware)->returns($this->dispatcher1);

                    $this->delegate->__invoke->with($this->dispatcher1, $this->middleware)->returns($this->dispatcher2);

                    $test = ($this->resolver->get())($this->handler, $middleware);

                    expect($test)->toBe($this->dispatcher2);

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
