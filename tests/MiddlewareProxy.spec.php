<?php

use function Eloquent\Phony\Kahlan\mock;
use function Eloquent\Phony\Kahlan\stub;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;

use Ellipse\Dispatcher\MiddlewareProxy;
use Ellipse\Dispatcher\Exceptions\MiddlewareResolvingException;

describe('MiddlewareProxy', function () {

    it('should implement MiddlewareInterface', function () {

        $proxy = new MiddlewareProxy('element');

        expect($proxy)->toBeAnInstanceOf(MiddlewareInterface::class);

    });

    describe('->process()', function () {

        beforeEach(function () {

            $this->middleware = mock(MiddlewareInterface::class);

            $this->request = mock(ServerRequestInterface::class)->get();
            $this->response = mock(ResponseInterface::class)->get();
            $this->handler = mock(RequestHandlerInterface::class)->get();

        });

        context('when the element is an instance of MiddlewareInterface', function () {

            it('should proxy the middleware ->process() method', function () {

                $proxy = new MiddlewareProxy($this->middleware->get());

                $this->middleware->process->with($this->request, $this->handler)
                    ->returns($this->response);

                $test = $proxy->process($this->request, $this->handler);

                expect($test)->toEqual($this->response);
                $this->middleware->process->called();

            });

        });

        context('when the element is not an instance of MiddlewareInterface', function () {

            context('when the resolver is not null', function () {

                beforeEach(function () {

                    $this->resolver = stub();

                    $this->proxy = new MiddlewareProxy('element', $this->resolver);

                });

                context('when the resolver resolve the element as an instance of MiddlewareInterface', function () {

                    it('should proxy the resolved middleware ->process() method', function () {

                        $this->resolver->with('element')->returns($this->middleware);

                        $this->middleware->process->with($this->request, $this->handler)
                            ->returns($this->response);

                        $test = $this->proxy->process($this->request, $this->handler);

                        expect($test)->toEqual($this->response);
                        $this->resolver->called();
                        $this->middleware->process->called();

                    });

                });

                context('when the resolver does not resolve the element as an instance of MiddlewareInterface', function () {

                    it('should throw MiddlewareResolvingException', function () {

                        $this->resolver->with('element')->returns(null);

                        $test = function () {

                            $this->proxy->process($this->request, $this->handler);

                        };

                        expect($test)->toThrow(new MiddlewareResolvingException('element'));
                        $this->resolver->called();

                    });

                });

            });

            context('when the resolver is null', function () {

                it('should throw MiddlewareResolvingException', function () {

                    $proxy = new MiddlewareProxy('element');

                    $test = function () use ($proxy) {

                        $proxy->process($this->request, $this->handler);

                    };

                    expect($test)->toThrow(new MiddlewareResolvingException('element'));

                });

            });

        });

    });

});
