<?php

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;

use Ellipse\Dispatcher\Delegate;
use Ellipse\Contracts\Dispatcher\Exceptions\InvalidMiddlewareReturnValueException;

describe('Delegate', function () {

    beforeEach(function () {

        $this->request = Mockery::mock(ServerRequestInterface::class);
        $this->response = Mockery::mock(ResponseInterface::class);
        $this->middleware = Mockery::mock(MiddlewareInterface::class);
        $this->next = Mockery::mock(DelegateInterface::class);
        $this->delegate = new Delegate($this->middleware, $this->next);

    });

    it('should implements DelegateInterface', function () {

        expect($this->delegate)->to->be->an->instanceof(DelegateInterface::class);

    });

    describe('->process()', function () {

        it('should return the value produced by the injected middleware', function () {

            $this->middleware->shouldReceive('process')
                ->with($this->request, $this->next)
                ->andReturn($this->response);

            $test = $this->delegate->process($this->request);

            expect($test)->to->be->equal($this->response);

        });

        it('should fail when the injected middleware does not produce an instance of ResponseInterface', function () {

            $this->middleware->shouldReceive('process')
                ->with($this->request, $this->next)
                ->andReturn('test');

            $test = function ($request) {

                $this->delegate->process($request);

            };

            expect($test)->with($this->request)->to->throw(InvalidMiddlewareReturnValueException::class);

        });

    });

});
