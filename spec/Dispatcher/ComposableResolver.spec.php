<?php

use function Eloquent\Phony\Kahlan\mock;

use Ellipse\Dispatcher;
use Ellipse\DispatcherFactoryInterface;
use Ellipse\Dispatcher\ComposableResolver;
use Ellipse\Dispatcher\ResolverWithMiddleware;

describe('ComposableResolver', function () {

    beforeEach(function () {

        $this->delegate = mock(DispatcherFactoryInterface::class);

        $this->resolver = new ComposableResolver($this->delegate->get());

    });

    it('should implement DispatcherFactoryInterface', function () {

        expect($this->resolver)->toBeAnInstanceOf(DispatcherFactoryInterface::class);

    });

    describe('->with()', function () {

        it ('should return a new ResolverWithMiddleware using the delegate and the given middleware queue', function () {

            $middleware = ['middleware1', 'middleware2'];

            $test = $this->resolver->with($middleware);

            $resolver = new ResolverWithMiddleware($this->delegate->get(), $middleware);

            expect($test)->toEqual($resolver);

        });

    });

    describe('->__invoke()', function () {

        beforeEach(function () {

            $this->dispatcher = mock(Dispatcher::class)->get();

        });

        context('when no middleware queue is given', function () {

            it('should proxy the delegate with the given request handler and an empty array of middleware', function () {

                $this->delegate->__invoke->with('handler', [])->returns($this->dispatcher);

                $test = ($this->resolver)('handler');

                expect($test)->toBe($this->dispatcher);

            });

        });

        context('when an middleware queue is given', function () {

            it('should proxy the delegate with the given request handler and middleware queue', function () {

                $middleware = ['middleware1', 'middleware2'];

                $this->delegate->__invoke->with('handler', $middleware)->returns($this->dispatcher);

                $test = ($this->resolver)('handler', $middleware);

                expect($test)->toBe($this->dispatcher);

            });

        });

    });

});
