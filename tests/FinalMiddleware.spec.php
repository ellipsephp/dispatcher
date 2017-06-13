<?php

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;

use Ellipse\Dispatcher\FinalMiddleware;
use Ellipse\Dispatcher\FinalDelegate;

describe('FinalMiddleware', function () {

    beforeEach(function () {

        $this->request = Mockery::mock(ServerRequestInterface::class);
        $this->response = Mockery::mock(ResponseInterface::class);
        $this->delegate = Mockery::mock(FinalDelegate::class);
        $this->delegate = Mockery::mock(DelegateInterface::class);
        $this->middleware = new FinalMiddleware($this->delegate);

    });

    afterEach(function () {

        Mockery::close();

    });

    it('should implements MiddlewareInterface', function () {

        expect($this->middleware)->to->be->an->instanceof(MiddlewareInterface::class);

    });

    describe('->process()', function () {

        it('should proxy the process method of the underlying delegate', function () {

            $this->delegate->shouldReceive('process')->once()
                ->with($this->request)
                ->andReturn($this->response);

            $test = $this->middleware->process($this->request, $this->delegate);

            expect($test)->to->be->equal($this->response);

        });

    });

});
