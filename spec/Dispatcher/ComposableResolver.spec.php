<?php

use function Eloquent\Phony\Kahlan\mock;

use Ellipse\Dispatcher;
use Ellipse\DispatcherFactoryInterface;
use Ellipse\Dispatcher\ComposableResolver;
use Ellipse\Dispatcher\ResolverWithMiddleware;
use Ellipse\Dispatcher\ComposableResolverInterface;

describe('ComposableResolver', function () {

    beforeEach(function () {

        $this->delegate = mock(DispatcherFactoryInterface::class);

        $this->factory = new ComposableResolver($this->delegate->get());

    });

    it('should implement DispatcherFactoryInterface', function () {

        expect($this->factory)->toBeAnInstanceOf(DispatcherFactoryInterface::class);

    });

    it('should implement ComposableResolverInterface', function () {

        expect($this->factory)->toBeAnInstanceOf(ComposableResolverInterface::class);

    });

    describe('->with()', function () {

        it ('should return a new ResolverWithMiddleware using this factory as delegate and the given iterable middleware queue', function () {

            $test = function ($middleware) {

                $test = $this->factory->with($middleware);

                $resolver = new ResolverWithMiddleware($this->factory, $middleware);

                expect($test)->toEqual($resolver);

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

    describe('->__invoke()', function () {

        beforeEach(function () {

            $this->dispatcher = mock(Dispatcher::class)->get();

        });

        context('when no iterable middleware queue is given', function () {

            it('should proxy a new UnresolvedDispatcher with the given request handler and an empty array of middleware', function () {

                $this->delegate->__invoke->with('handler', [])->returns($this->dispatcher);

                $test = ($this->factory)('handler');

                expect($test)->toBe($this->dispatcher);

            });

        });

        context('when an iterable middleware queue is given', function () {

            it('should proxy a new UnresolverDispatcher with the given request handler and iterable middleware queue', function () {

                $test = function ($middleware) {

                    $this->delegate->__invoke->with('handler', $middleware)->returns($this->dispatcher);

                    $test = ($this->factory)('handler', $middleware);

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

});
