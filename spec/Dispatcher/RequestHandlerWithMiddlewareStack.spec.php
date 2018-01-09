<?php

use function Eloquent\Phony\Kahlan\mock;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;

use Ellipse\Dispatcher\RequestHandlerWithMiddlewareStack;

describe('RequestHandlerWithMiddlewareStack', function () {

    beforeEach(function () {

        $this->delegate = mock(RequestHandlerInterface::class);

    });

    it('should implement RequestHandlerInterface', function () {

        $handler = new RequestHandlerWithMiddlewareStack($this->delegate->get(), []);

        expect($handler)->toBeAnInstanceOf(RequestHandlerInterface::class);

    });

    describe('->handle()', function () {

        beforeEach(function () {

            $this->request1 = mock(ServerRequestInterface::class);
            $this->request2 = mock(ServerRequestInterface::class);
            $this->request3 = mock(ServerRequestInterface::class);
            $this->response1 = mock(ResponseInterface::class);
            $this->response2 = mock(ResponseInterface::class);
            $this->response3 = mock(ResponseInterface::class);

        });

        it('should process the array of middleware as a stack (last in first out)', function () {

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

            $this->request1->withAttribute->with('key2', 'value2')->returns($this->request2);
            $this->request2->withAttribute->with('key1', 'value1')->returns($this->request3);
            $this->response1->withHeader->with('key1', 'value1')->returns($this->response2);
            $this->response2->withHeader->with('key2', 'value2')->returns($this->response3);
            $this->delegate->handle->with($this->request3)->returns($this->response1);

            $handler = new RequestHandlerWithMiddlewareStack($this->delegate->get(), [
                $this->middleware1,
                $this->middleware2,
            ]);

            $test = $handler->handle($this->request1->get());

            expect($test)->toBe($this->response3->get());

        });

        context('when the array of middleware is empty', function () {

            it('should proxy the delegate', function () {

                $this->delegate->handle->with($this->request1)->returns($this->response1);

                $handler = new RequestHandlerWithMiddlewareStack($this->delegate->get(), []);

                $test = $handler->handle($this->request1->get());

                expect($test)->toBe($this->response1->get());

            });

        });

    });

});
