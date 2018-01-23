<?php

use function Eloquent\Phony\Kahlan\mock;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

use Ellipse\Dispatcher\FallbackResponse;

describe('FallbackResponse', function () {

    beforeEach(function () {

        $this->response = mock(ResponseInterface::class)->get();

        $this->prototype = new FallbackResponse($this->response);

    });

    it('should implement RequestHandlerInterface', function () {

        expect($this->prototype)->toBeAnInstanceOf(RequestHandlerInterface::class);

    });

    describe('->handle()', function () {

        it('should return the response', function () {

            $request = mock(ServerRequestInterface::class)->get();

            $test = $this->prototype->handle($request);

            expect($test)->toBe($this->response);

        });

    });

});
