<?php

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;

use Ellipse\Dispatcher\RequestHandler;
use Ellipse\Dispatcher\Exceptions\InvalidMiddlewareReturnValueException;

describe('RequestHandler', function () {

    beforeEach(function () {

        $this->request = Mockery::mock(ServerRequestInterface::class);
        $this->response = Mockery::mock(ResponseInterface::class);
        $this->middleware = Mockery::mock(MiddlewareInterface::class);
        $this->next = Mockery::mock(RequestHandlerInterface::class);
        $this->handler = new RequestHandler($this->middleware, $this->next);

    });

    afterEach(function () {

        Mockery::close();

    });

    it('should implements RequestHandlerInterface', function () {

        expect($this->handler)->to->be->an->instanceof(RequestHandlerInterface::class);

    });

    describe('->process()', function () {

        it('should proxy the process method of the underlying middleware', function () {

            $this->middleware->shouldReceive('process')->once()
                ->with($this->request, $this->next)
                ->andReturn($this->response);

            $test = $this->handler->handle($this->request);

            expect($test)->to->be->equal($this->response);

        });

        it('should fail when the injected middleware does not produce an instance of ResponseInterface', function () {

            $this->middleware->shouldReceive('process')->once()
                ->with($this->request, $this->next)
                ->andReturn('test');

            expect([$this->handler, 'handle'])->with($this->request)
                ->to->throw(InvalidMiddlewareReturnValueException::class);

        });

    });

});
