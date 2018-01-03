<?php

use function Eloquent\Phony\Kahlan\mock;

use Interop\Http\Server\RequestHandlerInterface;

use Ellipse\Dispatcher;
use Ellipse\DispatcherFactoryInterface;
use Ellipse\Dispatcher\UnresolvedDispatcher;

describe('UnresolvedDispatcher', function () {

    describe('->value()', function () {

        beforeEach(function () {

            $this->factory = mock(DispatcherFactoryInterface::class);

            $this->dispatcher = mock(Dispatcher::class)->get();

        });

        context('when the request handler is an UnresolvedDispatcher', function () {

            it('should proxy the factory with the request handler produced by the unresolved dispatcher ->value() method', function () {

                $test = function ($middleware) {

                    $dispatcher1 = mock(UnresolvedDispatcher::class);
                    $dispatcher2 = mock(Dispatcher::class)->get();

                    $dispatcher1->value->with($this->factory)->returns($dispatcher2);

                    $dispatcher = new UnresolvedDispatcher($middleware, $dispatcher1->get());

                    $this->factory->__invoke->with($dispatcher2, $middleware)->returns($this->dispatcher);

                    $test = $dispatcher->value($this->factory->get());

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

        context('when the request handler is not an UnresolvedDispatcher', function () {

            it('should proxy the factory with the request handler', function () {

                $test = function ($middleware) {

                    $handler = mock(RequestHandlerInterface::class)->get();

                    $dispatcher = new UnresolvedDispatcher($middleware, $handler);

                    $this->factory->__invoke->with($handler, $middleware)->returns($this->dispatcher);

                    $test = $dispatcher->value($this->factory->get());

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
