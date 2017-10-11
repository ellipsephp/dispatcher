<?php

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;

use Ellipse\Dispatcher\MiddlewareProxy;
use Ellipse\Dispatcher\Exceptions\MiddlewareResolvingException;

interface MiddlewareProxyCallable
{
    public function __invoke();
}

describe('MiddlewareProxy', function () {

    afterEach(function () {

        Mockery::close();

    });

    it('should implement MiddlewareInterface', function () {

        $proxy = new MiddlewareProxy('element');

        expect($proxy)->to->be->an->instanceof(MiddlewareInterface::class);

    });

    describe('->process()', function () {

        beforeEach(function () {

            $this->request = Mockery::mock(ServerRequestInterface::class);
            $this->response = Mockery::mock(ResponseInterface::class);
            $this->handler = Mockery::mock(RequestHandlerInterface::class);

        });

        context('when the element is an instance of MiddlewareInterface', function () {

            beforeEach(function () {

                $this->middleware = Mockery::mock(MiddlewareInterface::class);

                $this->proxy = new MiddlewareProxy($this->middleware);

            });

            it('should proxy the middleware ->process() method', function () {

                $this->middleware->shouldReceive('process')->once()
                    ->with($this->request, $this->handler)
                    ->andReturn($this->response);

                $test = $this->proxy->process($this->request, $this->handler);

                expect($test)->to->be->equal($this->response);

            });

        });

        context('when the element is not an instance of MiddlewareInterface', function () {

            context('when the resolver is not null', function () {

                beforeEach(function () {

                    $this->resolver = Mockery::mock(MiddlewareProxyCallable::class);

                    $this->proxy = new MiddlewareProxy('element', $this->resolver);

                });

                context('when the resolver resolve the element as an instance of MiddlewareInterface', function () {

                    beforeEach(function () {

                        $this->middleware = Mockery::mock(MiddlewareInterface::class);

                        $this->resolver->shouldReceive('__invoke')->once()
                            ->with('element')
                            ->andReturn($this->middleware);

                    });

                    it('should proxy the resolved middleware ->process() method', function () {

                        $this->middleware->shouldReceive('process')->once()
                            ->with($this->request, $this->handler)
                            ->andReturn($this->response);

                        $test = $this->proxy->process($this->request, $this->handler);

                        expect($test)->to->be->equal($this->response);

                    });

                });

                context('when the resolver does not resolve the element as an instance of MiddlewareInterface', function () {

                    beforeEach(function () {

                        $this->resolver->shouldReceive('__invoke')->once()
                            ->with('element')
                            ->andReturn(null);

                    });

                    it('should fail', function () {

                        expect([$this->proxy, 'process'])->with($this->request, $this->handler)
                            ->to->throw(MiddlewareResolvingException::class);

                    });

                });

            });

            context('when the resolver is null', function () {

                beforeEach(function () {

                    $this->proxy = new MiddlewareProxy('element');

                });

                it('should fail', function () {

                    expect([$this->proxy, 'process'])->with($this->request, $this->handler)
                        ->to->throw(MiddlewareResolvingException::class);

                });

            });

        });

    });

});
