<?php

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;

use Ellipse\Stack\FinalMiddleware;
use Ellipse\Stack\FinalDelegate;

describe('FinalMiddleware', function () {

    beforeEach(function () {

        $this->delegate = Mockery::mock(DelegateInterface::class);
        $this->middleware = new FinalMiddleware($this->delegate);

    });

    it('should implements MiddlewareInterface', function () {

        expect($this->middleware)->to->be->an->instanceof(MiddlewareInterface::class);

    });

    describe('->process()', function () {

        it('should process the request with the injected instance of DelegateInterface', function () {

            $request = Mockery::mock(ServerRequestInterface::class);
            $response = Mockery::mock(ResponseInterface::class);
            $delegate = Mockery::mock(FinalDelegate::class);

            $this->delegate->shouldReceive('process')
                ->with($request)
                ->andReturn($response);

            $delegate->shouldNotReceive('process');

            $test = $this->middleware->process($request, $delegate);

            expect($test)->to->be->equal($response);

        });

    });

});
