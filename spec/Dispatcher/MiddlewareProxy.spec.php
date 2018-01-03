<?php

use function Eloquent\Phony\Kahlan\mock;

use Interop\Http\Server\MiddlewareInterface;

use Ellipse\Dispatcher\MiddlewareProxy;
use Ellipse\Dispatcher\Exceptions\MiddlewareResolvingException;

describe('MiddlewareProxy', function () {

    it('should be an instance of Traversable', function () {

        $test = new MiddlewareProxy(['middleware1', 'middleware2']);

        expect($test)->toBeAnInstanceOf(Traversable::class);

    });

    context('when transformed to an array via iterator_to_array()', function () {

        context('when all the items in the iterable list of middleware implement MiddlewareInterface', function () {

            beforeEach(function () {

                $this->middleware1 = mock(MiddlewareInterface::class)->get();
                $this->middleware2 = mock(MiddlewareInterface::class)->get();
                $this->middleware3 = mock(MiddlewareInterface::class)->get();

                $this->middleware = [
                    $this->middleware1,
                    $this->middleware2,
                    $this->middleware3,
                ];

            });

            it('should produce an array containing all the middleware', function () {

                $test = function ($middleware) {

                    $proxy = new MiddlewareProxy($middleware);

                    $test = iterator_to_array($proxy);

                    expect($test[0])->toBe($this->middleware1);
                    expect($test[1])->toBe($this->middleware2);
                    expect($test[2])->toBe($this->middleware3);

                };

                $test($this->middleware);
                $test(new ArrayIterator($this->middleware));
                $test(new class ($this->middleware) implements IteratorAggregate
                {
                    public function __construct($middleware) { $this->middleware = $middleware; }
                    public function getIterator() { return new ArrayIterator($this->middleware); }
                });

            });

            it('should not fail when used multiple time with iterator_to_array()', function () {

                $test = function ($middleware) {

                    $proxy = new MiddlewareProxy($middleware);

                    $test = iterator_to_array($proxy);

                    expect($test[0])->toBe($this->middleware1);
                    expect($test[1])->toBe($this->middleware2);
                    expect($test[2])->toBe($this->middleware3);

                    $test = iterator_to_array($proxy);

                    expect($test[0])->toBe($this->middleware1);
                    expect($test[1])->toBe($this->middleware2);
                    expect($test[2])->toBe($this->middleware3);

                };

                $test($this->middleware);
                $test(new ArrayIterator($this->middleware));
                $test(new class ($this->middleware) implements IteratorAggregate
                {
                    public function __construct($middleware) { $this->middleware = $middleware; }
                    public function getIterator() { return new ArrayIterator($this->middleware); }
                });

            });

        });

        context('when some of the items in the iterable list of middleware do not implement MiddlewareInterface', function () {

            it('should throw a MiddlewareResolvingException', function () {

                $test = function ($middleware) {

                    $test = function () use ($middleware) {

                        $proxy = new MiddlewareProxy($middleware);

                        $test = iterator_to_array($proxy);

                    };

                    $exception = new MiddlewareResolvingException('middleware2');

                    expect($test)->toThrow($exception);

                };

                $middleware = [
                    mock(MiddlewareInterface::class)->get(),
                    'middleware2',
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

    });

});
