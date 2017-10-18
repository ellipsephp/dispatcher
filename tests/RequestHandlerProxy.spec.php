<?php

use function Eloquent\Phony\Kahlan\mock;
use function Eloquent\Phony\Kahlan\stub;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Interop\Http\Server\RequestHandlerInterface;

use Ellipse\Dispatcher\RequestHandlerProxy;
use Ellipse\Dispatcher\Exceptions\RequestHandlerResolvingException;

describe('RequestHandlerProxy', function () {

    it('should implement RequestHandlerInterface', function () {

        $proxy = new RequestHandlerProxy('element');

        expect($proxy)->toBeAnInstanceOf(RequestHandlerInterface::class);

    });

    describe('->handle()', function () {

        beforeEach(function () {

            $this->handler = mock(RequestHandlerInterface::class);

            $this->request = mock(ServerRequestInterface::class)->get();
            $this->response = mock(ResponseInterface::class)->get();

        });

        context('when the element is an instance of RequestHandlerInterface', function () {

            it('should proxy the handler ->handle() method', function () {

                $proxy = new RequestHandlerProxy($this->handler->get());

                $this->handler->handle->with($this->request)->returns($this->response);

                $test = $proxy->handle($this->request);

                expect($test)->toEqual($this->response);
                $this->handler->handle->called();

            });

        });

        context('when the element is not an instance of RequestHandlerInterface', function () {

            context('when the resolver is not null', function () {

                beforeEach(function () {

                    $this->resolver = stub();

                    $this->proxy = new RequestHandlerProxy('element', $this->resolver);

                });

                context('when the resolver resolve the element as an instance of RequestHandlerInterface', function () {

                    it('should proxy the resolved handler ->handle() method', function () {

                        $this->resolver->with('element')->returns($this->handler);

                        $this->handler->handle->with($this->request)->returns($this->response);

                        $test = $this->proxy->handle($this->request);

                        expect($test)->toEqual($this->response);
                        $this->resolver->called();

                    });

                });

                context('when the resolver does not resolve the element as an instance of RequestHandlerInterface', function () {

                    it('should throw RequestHandlerResolvingException', function () {

                        $this->resolver->with('element')->returns(null);

                        $test = function () {

                            $this->proxy->handle($this->request);

                        };

                        expect($test)->toThrow(new RequestHandlerResolvingException('element'));
                        $this->resolver->called();

                    });

                });

            });

            context('when the resolver is null', function () {

                it('should throw RequestHandlerResolvingException', function () {

                    $proxy = new RequestHandlerProxy('element');

                    $test = function () use ($proxy) {

                        $proxy->handle($this->request);

                    };

                    expect($test)->toThrow(new RequestHandlerResolvingException('element'));

                });

            });

        });

    });

});
