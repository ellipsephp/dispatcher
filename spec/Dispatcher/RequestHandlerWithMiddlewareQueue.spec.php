<?php

use function Eloquent\Phony\Kahlan\mock;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use Ellipse\Dispatcher\RequestHandlerWithMiddlewareQueue;
use Ellipse\Dispatcher\Exceptions\MiddlewareTypeException;

describe('RequestHandlerWithMiddlewareQueue', function () {

    beforeEach(function () {

        $this->request1 = mock(ServerRequestInterface::class);
        $this->request2 = mock(ServerRequestInterface::class);
        $this->request3 = mock(ServerRequestInterface::class);
        $this->response1 = mock(ResponseInterface::class);
        $this->response2 = mock(ResponseInterface::class);
        $this->response3 = mock(ResponseInterface::class);

        $this->delegate = mock(RequestHandlerInterface::class);

        $this->middleware1 = new class implements MiddlewareInterface
        {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                $request = $request->withAttribute('key1', 'value1');

                $response = $handler->handle($request);

                return $response->withHeader('key1', 'value1');
            }
        };

        $this->middleware2 = new class implements MiddlewareInterface
        {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                $request = $request->withAttribute('key2', 'value2');

                $response = $handler->handle($request);

                return $response->withHeader('key2', 'value2');
            }
        };

    });

    it('should implement RequestHandlerInterface', function () {

        $handler = new RequestHandlerWithMiddlewareQueue($this->delegate->get(), []);

        expect($handler)->toBeAnInstanceOf(RequestHandlerInterface::class);

    });

    describe('__construct()', function () {

        context('when a middleware in the given array of middleware does not implement MiddlewareInterface', function () {

            it('should throw a MiddlewareTypeException', function () {

                $this->request1->withAttribute->with('key1', 'value1')->returns($this->request2);

                $test = function () {

                    new RequestHandlerWithMiddlewareQueue($this->delegate->get(), [
                        $this->middleware1,
                        'middleware',
                    ]);

                };

                $exception = new MiddlewareTypeException('middleware', mock(TypeError::class)->get());

                expect($test)->toThrow($exception);

            });

        });

    });

    describe('->handle()', function () {

        it('should process the array of middleware as a queue (first in first out)', function () {

            $this->request1->withAttribute->with('key1', 'value1')->returns($this->request2);
            $this->request2->withAttribute->with('key2', 'value2')->returns($this->request3);
            $this->response1->withHeader->with('key2', 'value2')->returns($this->response2);
            $this->response2->withHeader->with('key1', 'value1')->returns($this->response3);
            $this->delegate->handle->with($this->request3)->returns($this->response1);

            $handler = new RequestHandlerWithMiddlewareQueue($this->delegate->get(), [
                $this->middleware1,
                $this->middleware2,
            ]);

            $test = $handler->handle($this->request1->get());

            expect($test)->toBe($this->response3->get());

        });

        context('when the array of middleware is empty', function () {

            it('should proxy the delegate', function () {

                $this->delegate->handle->with($this->request1)->returns($this->response1);

                $handler = new RequestHandlerWithMiddlewareQueue($this->delegate->get(), []);

                $test = $handler->handle($this->request1->get());

                expect($test)->toBe($this->response1->get());

            });

        });

    });

});
