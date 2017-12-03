<?php

use function Eloquent\Phony\Kahlan\stub;
use function Eloquent\Phony\Kahlan\mock;

use Interop\Http\Server\MiddlewareInterface;

use Ellipse\Dispatcher\MiddlewareStack;
use Ellipse\Dispatcher\MiddlewareProxy;
use Ellipse\Dispatcher\Exceptions\MiddlewareStackExhaustedException;

describe('MiddlewareStack', function () {

    context('when the list of elements is empty', function () {

        beforeEach(function () {

            $this->stack = new MiddlewareStack([]);

        });

        describe('->isEmpty()', function () {

            it('should return true', function () {

                $test = $this->stack->isEmpty();

                expect($test)->toBeTruthy();

            });

        });

        describe('->head()', function () {

            it('should throw MiddlewareStackExhaustedException', function () {

                $test = function () { $this->stack->head(); };

                $exception = new MiddlewareStackExhaustedException;

                expect($test)->toThrow($exception);

            });

        });

        describe('->tail()', function () {

            it('should throw MiddlewareStackExhaustedException', function () {

                $test = function () { $this->stack->tail(); };

                $exception = new MiddlewareStackExhaustedException;

                expect($test)->toThrow($exception);

            });

        });

    });

    context('when the list of elements is not empty', function () {

        beforeEach(function () {

            $this->middleware1 = mock(MiddlewareInterface::class)->get();
            $this->middleware2 = mock(MiddlewareInterface::class)->get();

            $this->resolver = stub();

            $this->stack = new MiddlewareStack([$this->middleware1, $this->middleware2], $this->resolver);

        });

        describe('->isEmpty()', function () {

            it('should return false', function () {

                $test = $this->stack->isEmpty();

                expect($test)->toBeFalsy();

            });

        });

        describe('->head()', function () {

            it('should return a new MiddlewareProxy wrapped around the first element', function () {

                $test = $this->stack->head();

                $head = new MiddlewareProxy($this->middleware1, $this->resolver);

                expect($test)->toEqual($head);

            });

        });

        describe('->tail()', function () {

            it('should return a new MiddlewareStack containing the list of elements without the first one', function () {

                $test = $this->stack->tail();

                $tail = new MiddlewareStack([$this->middleware2], $this->resolver);

                expect($test)->toEqual($tail);
                expect($test)->not->toBe($this->stack);

            });
        });
    });
});
