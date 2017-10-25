<?php

use function Eloquent\Phony\Kahlan\mock;
use function Eloquent\Phony\Kahlan\anInstanceOf;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;

use Ellipse\Dispatcher;
use Ellipse\Dispatcher\MiddlewareStack;
use Ellipse\Dispatcher\Exceptions\InvalidReturnValueException;

describe('Dispatcher', function () {

    beforeEach(function () {

        $this->stack = mock(MiddlewareStack::class);
        $this->handler = mock(RequestHandlerInterface::class);

        $this->dispatcher = new Dispatcher($this->stack->get(), $this->handler->get());

    });

    it('should implement RequestHandlerInterface', function () {

        expect($this->dispatcher)->toBeAnInstanceOf(RequestHandlerInterface::class);

    });

    describe('->handle()', function () {

        beforeEach(function () {

            $this->request = mock(ServerRequestInterface::class)->get();
            $this->response = mock(ResponseInterface::class)->get();

        });

        context('when the middleware stack is empty', function () {

            beforeEach(function () {

                $this->stack->isEmpty->returns(true);

            });

            context('when the handler ->handle() method returns an implementation of ResponseInterface', function () {

                it('should return it', function () {

                    $this->handler->handle->with($this->request)->returns($this->response);

                    $test = $this->dispatcher->handle($this->request);

                    expect($test)->toBe($this->response);

                });

            });

            context('when the handler ->handle() method does not return an implementation of ResponseInterface', function () {

                it('should throw InvalidReturnValueException', function () {

                    $this->handler->handle->with($this->request)->returns('invalid');

                    $test = function () {

                        $this->dispatcher->handle($this->request);

                    };

                    expect($test)->toThrow(new InvalidReturnValueException('invalid'));

                });

            });

        });

        context('when the middleware stack is not empty', function () {

            beforeEach(function () {

                $this->head = mock(MiddlewareInterface::class);
                $this->tail = mock(MiddlewareStack::class);

                $this->stack->isEmpty->returns(false);
                $this->stack->head->returns($this->head);
                $this->stack->tail->returns($this->tail);

            });

            context('when the head middleware ->process() method returns an implementation of ResponseInterface', function () {

                it('should return it', function () {

                    $this->head->process->with($this->request, anInstanceOf(Dispatcher::class))
                        ->returns($this->response);

                    $test = $this->dispatcher->handle($this->request);

                    expect($test)->toBe($this->response);

                });

            });

            context('when the head middleware ->process() method does not return an implementation of ResponseInterface', function () {

                it('should throw InvalidReturnValueException', function () {

                    $this->head->process->with($this->request, anInstanceOf(Dispatcher::class))
                        ->returns('invalid');

                    $test = function () {

                        $this->dispatcher->handle($this->request);

                    };

                    expect($test)->toThrow(new InvalidReturnValueException('invalid'));

                });

            });

        });

    });

});
