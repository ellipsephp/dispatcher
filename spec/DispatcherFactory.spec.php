<?php

use function Eloquent\Phony\Kahlan\stub;

use Ellipse\Dispatcher;
use Ellipse\DispatcherFactory;
use Ellipse\Dispatcher\MiddlewareStack;
use Ellipse\Dispatcher\RequestHandlerProxy;

describe('DispatcherFactory', function () {

    describe('->__invoke()', function () {

        context('when the dispatcher factory has middleware and handler resolvers', function () {

            it('should return a new Dispatcher using the resolvers', function () {

                $middleware = stub();
                $handler = stub();

                $this->factory = new DispatcherFactory($middleware, $handler);

                $test = ($this->factory)(['middleware'], 'handler');

                expect($test)->toEqual(new Dispatcher(
                    new MiddlewareStack(['middleware'], $middleware),
                    new RequestHandlerProxy('handler', $handler)
                ));

            });

        });

        context('when the dispatcher factory do not have middleware and handler resolver', function () {

            it('should return a new Dispatcher with no resolver', function () {

                $this->factory = new DispatcherFactory;

                $test = ($this->factory)(['middleware'], 'handler');

                expect($test)->toEqual(new Dispatcher(
                    new MiddlewareStack(['middleware'], null),
                    new RequestHandlerProxy('handler', null)
                ));

            });

        });

    });

});
