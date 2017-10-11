<?php

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;

use Ellipse\Dispatcher\Dispatcher;
use Ellipse\Dispatcher\MiddlewareStack;
use Ellipse\Dispatcher\Exceptions\InvalidReturnValueException;

describe('Dispatcher', function () {

    beforeEach(function () {

        $this->stack = Mockery::mock(MiddlewareStack::class);
        $this->handler = Mockery::mock(RequestHandlerInterface::class);

        $this->dispatcher = new Dispatcher($this->stack, $this->handler);

    });

    afterEach(function () {

        Mockery::close();

    });

    it('should implements RequestHandlerInterface', function () {

        expect($this->dispatcher)->to->be->an->instanceof(RequestHandlerInterface::class);

    });

    describe('::getInstance()', function () {

        it('should return a Dispatcher instance', function () {

            $test = Dispatcher::getInstance(['element'], $this->handler);

            expect($test)->to->be->an->instanceof(Dispatcher::class);

        });

    });

    describe('->handle()', function () {

        beforeEach(function () {

            $this->request = Mockery::mock(ServerRequestInterface::class);
            $this->response = Mockery::mock(ResponseInterface::class);

        });

        context('when the middleware stack is empty', function () {

            beforeEach(function () {

                $this->stack->shouldReceive('isEmpty')->once()->andReturn(true);

            });

            context('when the handler ->handle() method returns an implementation of ResponseInterface', function () {

                beforeEach(function () {

                    $this->handler->shouldReceive('handle')->once()
                        ->with($this->request)
                        ->andReturn($this->response);

                });

                it('should return it', function () {

                    $test = $this->dispatcher->handle($this->request);

                    expect($test)->to->be->equal($this->response);

                });

            });

            context('when the handler ->handle() method does not return an implementation of ResponseInterface', function () {

                beforeEach(function () {

                    $this->handler->shouldReceive('handle')->once()
                        ->with($this->request)
                        ->andReturn(null);

                });

                it('should fail', function () {

                    expect([$this->dispatcher, 'handle'])->with($this->request)
                        ->to->throw(InvalidReturnValueException::class);

                });

            });

        });

        context('when the middleware stack is not empty', function () {

            beforeEach(function () {

                $this->head = Mockery::mock(MiddlewareInterface::class);
                $this->tail = Mockery::mock(MiddlewareStack::class);

                $this->stack->shouldReceive('isEmpty')->once()->andReturn(false);
                $this->stack->shouldReceive('head')->once()->andReturn($this->head);
                $this->stack->shouldReceive('tail')->once()->andReturn($this->tail);

            });

            context('when the head middleware ->process() method returns an implementation of ResponseInterface', function () {

                beforeEach(function () {

                    $this->head->shouldReceive('process')->once()
                        ->with($this->request, Mockery::type(Dispatcher::class))
                        ->andReturn($this->response);

                });

                it('should return it', function () {

                    $test = $this->dispatcher->handle($this->request);

                    expect($test)->to->be->equal($this->response);

                });

            });

            context('when the head middleware ->process() method does not return an implementation of ResponseInterface', function () {

                beforeEach(function () {

                    $this->head->shouldReceive('process')->once()
                        ->with($this->request, Mockery::type(Dispatcher::class))
                        ->andReturn(null);

                });

                it('should fail', function () {

                    expect([$this->dispatcher, 'handle'])->with($this->request)
                        ->to->throw(InvalidReturnValueException::class);

                });

            });

        });

    });

});
