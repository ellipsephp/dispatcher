<?php

use function Eloquent\Phony\Kahlan\mock;

use Interop\Http\Server\MiddlewareInterface;

use Ellipse\Dispatcher\MiddlewareProxy;
use Ellipse\Dispatcher\MiddlewareStack;

describe('MiddlewareStack', function () {

    context('when the list of elements is empty', function () {

        beforeEach(function () {

            $this->stack = new MiddlewareStack([]);

        });

        describe('->isEmpty()', function () {

            it('should return true', function () {

                $test = $this->stack->isEmpty();

                expect($test)->toBe(true);

            });

        });

        describe('->head()', function () {

            it('should throw RuntimeException', function () {

                $test = function () { $this->stack->head(); };

                expect($test)->toThrow(new RuntimeException);

            });

        });

        describe('->tail()', function () {

            it('should throw RuntimeException', function () {

                $test = function () { $this->stack->tail(); };

                expect($test)->toThrow(new RuntimeException);

            });

        });

    });

    context('when the list of elements is not empty', function () {

        beforeEach(function () {

            $middleware1 = mock(MiddlewareInterface::class)->get();
            $middleware2 = mock(MiddlewareInterface::class)->get();

            $this->stack = new MiddlewareStack([$middleware1, $middleware2]);

        });

        describe('->isEmpty()', function () {

            it('should return false', function () {

                $test = $this->stack->isEmpty();

                expect($test)->toBe(false);

            });

        });

        describe('->head()', function () {

            it('should return a new MiddlewareProxy wrapped around the first element', function () {

                $test = $this->stack->head();

                expect($test)->toBeAnInstanceOf(MiddlewareProxy::class);

            });

        });

        describe('->tail()', function () {

            it('should return a new MiddlewareStack containing the list of elements without the first one', function () {

                $test = $this->stack->tail();

                expect($test)->toBeAnInstanceOf(MiddlewareStack::class);
                expect($test)->not->toEqual($this->stack);

            });
        });
    });
});
