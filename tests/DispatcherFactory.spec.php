<?php

use Ellipse\Dispatcher\DispatcherFactory;
use Ellipse\Dispatcher\Dispatcher;

describe('DispatcherFactory', function () {

    beforeEach(function () {

        $middleware = function () {};
        $handler = function () {};

        $this->factory = new DispatcherFactory($middleware, $handler);

    });

    describe('->__invoke()', function () {

        it('should return a new Dispatcher', function () {

            $test = ($this->factory)(['middleware'], 'handler');

            expect($test)->to->be->an->instanceof(Dispatcher::class);

        });

    });

});
