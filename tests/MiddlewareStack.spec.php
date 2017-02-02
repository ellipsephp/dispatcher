<?php

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;

use Ellipse\Contracts\Stack\MiddlewareStackInterface;
use Ellipse\Contracts\Resolver\ResolverInterface;

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

        it('should be an alias of ->withMiddleware()', function () {

            $element = 'test';

            $stack1 = Mockery::mock(MiddlewareStack::class . '[withMiddleware]', [
                $this->resolver,
            ]);

            $stack2 = Mockery::mock(MiddlewareStackInterface::class);

            $stack1->shouldReceive('withMiddleware')
                ->with($element)
                ->andReturn($stack2);

            $test = $stack1->with($element);

            expect($test)->to->be->equal($stack2);

        });

    });

    describe('->withMiddleware()', function () {

        it('should produce a new MiddlewareStack instance containing the element', function () {

            $element = 'test';

            $this->resolver->shouldReceive('resolve')
                ->with($element)
                ->andReturn($this->middleware);

            $this->middleware->shouldReceive('process')
                ->andReturn($this->response);

            $stack = $this->stack->withMiddleware($element);

            $ref_stack1 = &$this->stack;
            $ref_stack2 = &$stack;

            $test = $stack->withResolver($this->resolver)->dispatch($this->request);

            expect($stack)->to->be->an->instanceof(MiddlewareStack::class);
            expect($ref_stack1)->to->not->be->equal($ref_stack2);
            expect($test)->to->be->equal($this->response);

        });

    });

    describe('->withResolver()', function () {

        it('should return a new MiddlewareStack instance using the resolver', function () {

            $element = 'test';

            $this->resolver->shouldReceive('resolve')
                ->with($element)
                ->andReturn($this->middleware);

            $this->middleware->shouldReceive('process')
                ->andReturn($this->response);

            $stack = $this->stack->withResolver($this->resolver);

            $ref_stack1 = &$this->stack;
            $ref_stack2 = &$stack;

            $test = $stack->withMiddleware($element)->dispatch($this->request);

            expect($stack)->to->be->an->instanceof(MiddlewareStack::class);
            expect($ref_stack1)->to->not->be->equal($ref_stack2);
            expect($test)->to->be->equal($this->response);

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

            $value = $stack1->process($this->request, $this->delegate);

            expect($value)->to->be->equal($this->response);

        });

    });

});
