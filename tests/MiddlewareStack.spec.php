<?php

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;

use Ellipse\Contracts\Stack\MiddlewareStackInterface;
use Ellipse\Contracts\Stack\Exceptions\NoResponseReturnedException;

use Ellipse\Contracts\Resolver\ResolverInterface;
use Ellipse\Contracts\Resolver\Exceptions\ElementCantBeResolvedException;

use Ellipse\Stack\MiddlewareStack;
use Ellipse\Stack\FinalMiddleware;

describe('MiddlewareStack', function () {

    beforeEach(function () {

        $this->request = Mockery::mock(ServerRequestInterface::class);
        $this->response = Mockery::mock(ResponseInterface::class);
        $this->middleware = Mockery::mock(MiddlewareInterface::class);
        $this->resolver = Mockery::mock(ResolverInterface::class);
        $this->stack = new MiddlewareStack($this->resolver);

    });

    it('should implements MiddlewareStackInterface', function () {

        expect($this->stack)->to->be->an->instanceof(MiddlewareStackInterface::class);

    });

    describe('->with()', function () {

        it('should be an alias of ->withElement()', function () {

            $element = 'test';

            $stack1 = Mockery::mock(MiddlewareStack::class . '[withElement]', [
                $this->resolver,
            ]);

            $stack2 = Mockery::mock(MiddlewareStackInterface::class);

            $stack1->shouldReceive('withElement')
                ->with($element)
                ->andReturn($stack2);

            $test = $stack1->with($element);

            expect($test)->to->be->equal($stack2);

        });

    });

    describe('->withMiddleware()', function () {

        it('should produce a new MiddlewareStack instance containing the given instance of MiddlewareInterface', function () {

            $this->middleware->shouldReceive('process')
                ->andReturn($this->response);

            $stack = $this->stack->withMiddleware($this->middleware);

            expect($stack)->to->be->an->instanceof(MiddlewareStack::class);

            $ref_stack1 = &$this->stack;
            $ref_stack2 = &$stack;

            expect($ref_stack1)->to->not->be->equal($ref_stack2);

            $test = $stack->dispatch($this->request);

            expect($test)->to->be->equal($this->response);

            $cb = function ($stack, $request) {

                return $stack->dispatch($request);

            };

            expect($cb)->with($this->stack, $this->request)->to->throw(NoResponseReturnedException::class);

        });

    });

    describe('->withElement()', function () {

        it('should produce a new MiddlewareStack instance containing the given element', function () {

            $element = 'test';

            $this->resolver->shouldReceive('resolve')
                ->with($element)
                ->andReturn($this->middleware);

            $this->middleware->shouldReceive('process')
                ->andReturn($this->response);

            $stack = $this->stack->withElement($element);

            expect($stack)->to->be->an->instanceof(MiddlewareStack::class);

            $ref_stack1 = &$this->stack;
            $ref_stack2 = &$stack;

            expect($ref_stack1)->to->not->be->equal($ref_stack2);

            $test = $stack->dispatch($this->request);

            expect($test)->to->be->equal($this->response);

            $cb = function ($stack, $request) {

                return $stack->dispatch($request);

            };

            expect($cb)->with($this->stack, $this->request)->to->throw(NoResponseReturnedException::class);

        });

    });

    describe('->withResolver()', function () {

        it('should produce a new MiddlewareStack instance using the given instance of ResolverInterface', function () {

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

            $stack1 = new MiddlewareStack($resolver1, [$element]);
            $stack2 = $stack1->withResolver($resolver2);

            expect($stack2)->to->be->an->instanceof(MiddlewareStack::class);

            $ref_stack1 = &$stack1;
            $ref_stack2 = &$stack2;

            expect($ref_stack1)->to->not->be->equal($ref_stack2);

            $test = $stack2->dispatch($this->request);

            expect($test)->to->be->equal($this->response);

            $cb = function ($stack, $request) {

                return $stack->dispatch($request);

            };

            expect($cb)->with($stack1, $this->request)->to->throw(ElementCantBeResolvedException::class);

        });

    });

    describe('->process()', function () {

        it('should call dispatch on a new MiddlewareStack instance appended with a FinalMiddleware instance', function () {

            $stack1 = Mockery::mock(MiddlewareStack::class . '[withMiddleware]', [
                $this->resolver,
            ]);

            $stack2 = Mockery::mock(MiddlewareStackInterface::class);

            $stack1->shouldReceive('withMiddleware')
                ->with(Mockery::type(FinalMiddleware::class))
                ->andReturn($stack2);

            $stack2->shouldReceive('dispatch')
                ->with($this->request)
                ->andReturn($this->response);

            $test = $stack1->process($this->request, $this->delegate);

            expect($test)->to->be->equal($this->response);

        });

    });

});
