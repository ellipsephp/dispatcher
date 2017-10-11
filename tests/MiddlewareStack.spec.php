<?php

use Interop\Http\Server\MiddlewareInterface;

use Ellipse\Dispatcher\MiddlewareProxy;
use Ellipse\Dispatcher\MiddlewareStack;

interface MiddlewareStackCallable
{
    public function __invoke();
}

describe('MiddlewareStack', function () {

    afterEach(function () {

        Mockery::close();

    });

    context('when the list of elements is empty', function () {

        beforeEach(function () {

            $this->stack = new MiddlewareStack([]);

        });

        describe('->isEmpty()', function () {

            it('should return true', function () {

                $test = $this->stack->isEmpty();

                expect($test)->to->be->true();

            });

        });

        describe('->head()', function () {

            it('should fail', function () {

                expect([$this->stack, 'head'])->to->throw(RuntimeException::class);

            });

        });

        describe('->tail()', function () {

            it('should fail', function () {

                expect([$this->stack, 'tail'])->to->throw(RuntimeException::class);

            });

        });

    });

    context('when the list of elements is not empty', function () {

        beforeEach(function () {

            $middleware = Mockery::mock(MiddlewareInterface::class);

            $this->stack = new MiddlewareStack([$middleware]);

        });

        describe('->isEmpty()', function () {

            it('should return false', function () {

                $test = $this->stack->isEmpty();

                expect($test)->to->be->false();

            });

        });

        describe('->head()', function () {

            it('should return a new MiddlewareProxy wrapped around the first element', function () {

                $test = $this->stack->head();

                expect($test)->to->be->an->instanceof(MiddlewareProxy::class);

            });

        });

        describe('->tail()', function () {

            it('should return a new MiddlewareStack containing the list of elements without the first one', function () {

                $test = $this->stack->tail();

                expect($test)->to->be->an->instanceof(MiddlewareStack::class);
                expect($test)->to->not->be->equal($this->stack);

            });
        });
    });
});
