<?php

use Ellipse\Dispatcher;
use Ellipse\DispatcherFactory;

describe('DispatcherFactory', function () {

    beforeEach(function () {

        $middleware = function () {};
        $handler = function () {};

        $this->factory = new DispatcherFactory($middleware, $handler);

    });

    describe('->__invoke()', function () {

        it('should return a new Dispatcher', function () {

            $test = ($this->factory)(['middleware'], 'handler');

            expect($test)->toBeAnInstanceOf(Dispatcher::class);

        });

    });

});
