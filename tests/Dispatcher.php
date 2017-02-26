<?php

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;

use Ellipse\Contracts\Dispatcher\DispatcherInterface;
use Ellipse\Contracts\Dispatcher\Exceptions\NoResponseReturnedException;

use Ellipse\Contracts\Resolver\ResolverInterface;
use Ellipse\Contracts\Resolver\Exceptions\ElementCantBeResolvedException;

use Ellipse\Dispatcher\Dispatcher;
use Ellipse\Dispatcher\FinalMiddleware;

describe('Dispatcher', function () {

    beforeEach(function () {

        $this->request = Mockery::mock(ServerRequestInterface::class);
        $this->response = Mockery::mock(ResponseInterface::class);
        $this->middleware = Mockery::mock(MiddlewareInterface::class);
        $this->resolver = Mockery::mock(ResolverInterface::class);
        $this->dispatcher = new Dispatcher($this->resolver);

    });

    it('should implements DispatcherInterface', function () {

        expect($this->dispatcher)->to->be->an->instanceof(DispatcherInterface::class);

    });

    describe('->with()', function () {

        it('should be an alias of ->withElement()', function () {

            $element = 'test';

            $dispatcher1 = Mockery::mock(Dispatcher::class . '[withElement]', [
                $this->resolver,
            ]);

            $dispatcher2 = Mockery::mock(DispatcherInterface::class);

            $dispatcher1->shouldReceive('withElement')
                ->with($element)
                ->andReturn($dispatcher2);

            $test = $dispatcher1->with($element);

            expect($test)->to->be->equal($dispatcher2);

        });

    });

    describe('->withMiddleware()', function () {

        it('should produce a new Dispatcher instance containing the given instance of MiddlewareInterface', function () {

            $this->middleware->shouldReceive('process')
                ->andReturn($this->response);

            $dispatcher = $this->dispatcher->withMiddleware($this->middleware);

            expect($dispatcher)->to->be->an->instanceof(Dispatcher::class);

            $ref_stack1 = &$this->dispatcher;
            $ref_stack2 = &$dispatcher;

            expect($ref_stack1)->to->not->be->equal($ref_stack2);

            $test = $dispatcher->dispatch($this->request);

            expect($test)->to->be->equal($this->response);

            $cb = function ($dispatcher, $request) {

                return $dispatcher->dispatch($request);

            };

            expect($cb)->with($this->dispatcher, $this->request)->to->throw(NoResponseReturnedException::class);

        });

    });

    describe('->withElement()', function () {

        it('should produce a new Dispatcher instance containing the given element', function () {

            $element = 'test';

            $this->resolver->shouldReceive('resolve')
                ->with($element)
                ->andReturn($this->middleware);

            $this->middleware->shouldReceive('process')
                ->andReturn($this->response);

            $dispatcher = $this->dispatcher->withElement($element);

            expect($dispatcher)->to->be->an->instanceof(Dispatcher::class);

            $ref_stack1 = &$this->dispatcher;
            $ref_stack2 = &$dispatcher;

            expect($ref_stack1)->to->not->be->equal($ref_stack2);

            $test = $dispatcher->dispatch($this->request);

            expect($test)->to->be->equal($this->response);

            $cb = function ($dispatcher, $request) {

                return $dispatcher->dispatch($request);

            };

            expect($cb)->with($this->dispatcher, $this->request)->to->throw(NoResponseReturnedException::class);

        });

    });

    describe('->withResolver()', function () {

        it('should produce a new Dispatcher instance using the given instance of ResolverInterface', function () {

            $element = 'test';
            $resolver1 = Mockery::mock(ResolverInterface::class);
            $resolver2 = Mockery::mock(ResolverInterface::class);

            $resolver1->shouldReceive('resolve')
                ->with($element)
                ->andThrow(ElementCantBeResolvedException::class);

            $resolver2->shouldReceive('resolve')
                ->with($element)
                ->andReturn($this->middleware);

            $this->middleware->shouldReceive('process')
                ->andReturn($this->response);

            $dispatcher1 = new Dispatcher($resolver1, [$element]);
            $dispatcher2 = $dispatcher1->withResolver($resolver2);

            expect($dispatcher2)->to->be->an->instanceof(Dispatcher::class);

            $ref_stack1 = &$dispatcher1;
            $ref_stack2 = &$dispatcher2;

            expect($ref_stack1)->to->not->be->equal($ref_stack2);

            $test = $dispatcher2->dispatch($this->request);

            expect($test)->to->be->equal($this->response);

            $cb = function ($dispatcher, $request) {

                return $dispatcher->dispatch($request);

            };

            expect($cb)->with($dispatcher1, $this->request)->to->throw(ElementCantBeResolvedException::class);

        });

    });

    describe('->process()', function () {

        it('should call dispatch on a new Dispatcher instance appended with a FinalMiddleware instance', function () {

            $dispatcher1 = Mockery::mock(Dispatcher::class . '[withMiddleware]', [
                $this->resolver,
            ]);

            $dispatcher2 = Mockery::mock(DispatcherInterface::class);

            $dispatcher1->shouldReceive('withMiddleware')
                ->with(Mockery::type(FinalMiddleware::class))
                ->andReturn($dispatcher2);

            $dispatcher2->shouldReceive('dispatch')
                ->with($this->request)
                ->andReturn($this->response);

            $test = $dispatcher1->process($this->request, $this->delegate);

            expect($test)->to->be->equal($this->response);

        });

    });

});
