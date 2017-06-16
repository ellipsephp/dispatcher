<?php

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;

use Ellipse\Contracts\Dispatcher\DispatcherInterface;
use Ellipse\Contracts\Resolver\ResolverInterface;
use Ellipse\Dispatcher\Exceptions\NoResponseReturnedException;
use Ellipse\Dispatcher\Exceptions\InvalidMiddlewareReturnValueException;

use Ellipse\Dispatcher\Dispatcher;
use Ellipse\Dispatcher\FinalMiddleware;

describe('Dispatcher', function () {

    beforeEach(function () {

        $this->request = Mockery::mock(ServerRequestInterface::class);
        $this->response = Mockery::mock(ResponseInterface::class);
        $this->middleware = Mockery::mock(MiddlewareInterface::class);
        $this->resolver = Mockery::mock(ResolverInterface::class);
        $this->delegate = Mockery::mock(DelegateInterface::class);
        $this->dispatcher = new Dispatcher([], $this->resolver);

    });

    afterEach(function () {

        Mockery::close();

    });

    it('should implements DispatcherInterface', function () {

        expect($this->dispatcher)->to->be->an->instanceof(DispatcherInterface::class);

    });

    describe('->with()', function () {

        it('should produce a new Dispatcher instance containing the given element', function () {

            $this->resolver->shouldReceive('resolve')->once()
                ->with('element')
                ->andReturn($this->middleware);

            $this->middleware->shouldReceive('process')->once()
                ->with($this->request, Mockery::type(DelegateInterface::class))
                ->andReturn($this->response);

            $this->delegate->shouldNotReceive('process');

            $test1 = $this->dispatcher->with('element');

            expect($test1)->to->be->an->instanceof(DispatcherInterface::class);
            expect($test1)->to->not->be->equal($this->dispatcher);

            $test2 = $test1->process($this->request, $this->delegate);

            expect($test2)->to->be->equal($this->response);

        });

    });

    describe('->withResolver()', function () {

        it('should produce a new Dispatcher instance using the given instance of ResolverInterface', function () {

            $element = 'test';

            $resolver = Mockery::mock(ResolverInterface::class);

            $resolver->shouldReceive('resolve')->once()
                ->with($element)
                ->andReturn($this->middleware);

            $this->middleware->shouldReceive('process')->once()
                ->with($this->request, Mockery::type(DelegateInterface::class))
                ->andReturn($this->response);

            $this->delegate->shouldNotReceive('process');

            $test1 = $this->dispatcher->withResolver($resolver);

            expect($test1)->to->be->an->instanceof(Dispatcher::class);
            expect($test1)->to->not->be->equal($this->dispatcher);

            $test2 = $test1->with($element)->process($this->request, $this->delegate);

            expect($test2)->to->be->equal($this->response);

        });

    });

    describe('->process()', function () {

        it('should produce a new dispatcher with a final middleware and proxy its dispatch method', function () {

            $dispatcher1 = Mockery::mock(Dispatcher::class . '[with]');

            $dispatcher2 = Mockery::mock(DispatcherInterface::class);

            $dispatcher1->shouldReceive('with')->once()
                ->with(Mockery::type(FinalMiddleware::class))
                ->andReturn($dispatcher2);

            $dispatcher2->shouldReceive('dispatch')->once()
                ->with($this->request)
                ->andReturn($this->response);

            $test = $dispatcher1->process($this->request, $this->delegate);

            expect($test)->to->be->equal($this->response);

        });

        it('should return the response returned by the given delegate process method when empty', function () {

            $this->delegate->shouldReceive('process')->once()
                ->with($this->request)
                ->andReturn($this->response);

            $test = $this->dispatcher->process($this->request, $this->delegate);

            expect($test)->to->be->equal($this->response);

        });

    });

    describe('->dispatch()', function () {

        context('when the dispatcher contains valid elements', function () {

            beforeEach(function () {

                $this->element = 'element';

                $this->resolved = new class implements MiddlewareInterface
                {
                    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
                    {
                        return $delegate->process($request);
                    }
                };

                $this->middleware->shouldReceive('process')->once()
                    ->with($this->request, Mockery::type(DelegateInterface::class))
                    ->andReturn($this->response);

                $this->resolver->shouldReceive('resolve')->once()
                    ->with($this->element)
                    ->andReturn($this->resolved);

            });

            it('should return the response produced by the given middleware', function () {

                $test = $this->dispatcher
                    ->with($this->element)
                    ->with($this->middleware)
                    ->dispatch($this->request);

                expect($test)->to->be->equal($this->response);

            });

            it('should return the response produced by the given array of middleware', function () {

                $list = [$this->element, $this->middleware];

                $test = $this->dispatcher->with($list)->dispatch($this->request);

                expect($test)->to->be->equal($this->response);

            });

            it('should return the response produced by a traversable instance containing middleware', function () {

                $list = new ArrayObject([$this->element, $this->middleware]);

                $test = $this->dispatcher->with($list)->dispatch($this->request);

                expect($test)->to->be->equal($this->response);

            });

            it('should work recursively', function () {

                $list = [
                    clone($this->resolved),
                    [$this->element, $this->middleware],
                ];

                $test = $this->dispatcher->with($list)->dispatch($this->request);

                expect($test)->to->be->equal($this->response);

            });

        });

        context('when the dispatcher contains invalid elements', function () {

            it('should fail when empty', function () {

                expect([$this->dispatcher, 'dispatch'])->with($this->request)
                    ->to->throw(NoResponseReturnedException::class);

            });

            it('should fail when a middleware does not return an instance of ResponseInterface', function () {

                $this->middleware->shouldReceive('process')->once()
                    ->with($this->request, Mockery::type(DelegateInterface::class))
                    ->andReturn('test');

                $dispatcher = $this->dispatcher->with($this->middleware);

                expect([$dispatcher, 'dispatch'])->with($this->request)
                    ->to->throw(InvalidMiddlewareReturnValueException::class);

            });

        });

    });

});
