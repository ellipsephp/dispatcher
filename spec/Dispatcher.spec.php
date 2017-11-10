<?php

use function Eloquent\Phony\Kahlan\mock;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;

use Ellipse\Dispatcher;
use Ellipse\Dispatcher\MiddlewareStack;

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

        context('when the middleware stack is not empty', function () {

            it('should proxy the head middleware ->process() method', function () {

                $head = mock(MiddlewareInterface::class);

                $this->stack->isEmpty->returns(false);
                $this->stack->head->returns($head);

                $head->process->with($this->request, '~')->returns($this->response);

                $test = $this->dispatcher->handle($this->request);

                expect($test)->toBe($this->response);

            });

            it('should create a new dispatcher with the current middleware stack tail as middleware stack', function () {

                $tail = mock(MiddlewareInterface::class);

                $this->dispatcher->handle($this->request);

                $this->stack->tail->called();

            });

        });

        context('when the middleware stack is empty', function () {

            it('should proxy the handler ->handle() method', function () {

                $this->stack->isEmpty->returns(true);

                $this->handler->handle->with($this->request)->returns($this->response);

                $test = $this->dispatcher->handle($this->request);

                expect($test)->toBe($this->response);

            });

        });

    });

});
