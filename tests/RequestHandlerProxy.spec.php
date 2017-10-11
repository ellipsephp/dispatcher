<?php

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;

use Ellipse\Dispatcher\RequestHandlerProxy;
use Ellipse\Dispatcher\Exceptions\RequestHandlerResolvingException;

interface RequestHandlerProxyCallable
{
    public function __invoke();
}

describe('RequestHandlerProxy', function () {

    afterEach(function () {

        Mockery::close();

    });

    it('should implement RequestHandlerInterface', function () {

        $proxy = new RequestHandlerProxy('element');

        expect($proxy)->to->be->an->instanceof(RequestHandlerInterface::class);

    });

    describe('->handle()', function () {

        beforeEach(function () {

            $this->request = Mockery::mock(ServerRequestInterface::class);
            $this->response = Mockery::mock(ResponseInterface::class);

        });

        context('when the element is an instance of RequestHandlerInterface', function () {

            beforeEach(function () {

                $this->handler = Mockery::mock(RequestHandlerInterface::class);

                $this->proxy = new RequestHandlerProxy($this->handler);

            });

            it('should proxy the handler ->handle() method', function () {

                $this->handler->shouldReceive('handle')->once()
                    ->with($this->request)
                    ->andReturn($this->response);

                $test = $this->proxy->handle($this->request);

                expect($test)->to->be->equal($this->response);

            });

        });

        context('when the element is not an instance of RequestHandlerInterface', function () {

            context('when the resolver is not null', function () {

                beforeEach(function () {

                    $this->resolver = Mockery::mock(RequestHandlerProxyCallable::class);

                    $this->proxy = new RequestHandlerProxy('element', $this->resolver);

                });

                context('when the resolver resolve the element as an instance of RequestHandlerInterface', function () {

                    beforeEach(function () {

                        $this->handler = Mockery::mock(RequestHandlerInterface::class);

                        $this->resolver->shouldReceive('__invoke')->once()
                            ->with('element')
                            ->andReturn($this->handler);

                    });

                    it('should proxy the resolved handler ->handle() method', function () {

                        $this->handler->shouldReceive('handle')->once()
                            ->with($this->request)
                            ->andReturn($this->response);

                        $test = $this->proxy->handle($this->request);

                        expect($test)->to->be->equal($this->response);

                    });

                });

                context('when the resolver does not resolve the element as an instance of RequestHandlerInterface', function () {

                    beforeEach(function () {

                        $this->resolver->shouldReceive('__invoke')->once()
                            ->with('element')
                            ->andReturn(null);

                    });

                    it('should fail', function () {

                        expect([$this->proxy, 'handle'])->with($this->request)
                            ->to->throw(RequestHandlerResolvingException::class);

                    });

                });

            });

            context('when the resolver is null', function () {

                beforeEach(function () {

                    $this->proxy = new RequestHandlerProxy('element');

                });

                it('should fail', function () {

                    expect([$this->proxy, 'handle'])->with($this->request)
                        ->to->throw(RequestHandlerResolvingException::class);

                });

            });

        });

    });

});
