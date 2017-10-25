<?php

use function Eloquent\Phony\Kahlan\stub;

use Ellipse\Dispatcher;
use Ellipse\DispatcherFactory;

describe('DispatcherFactory', function () {

    beforeEach(function () {

        $this->factory = new DispatcherFactory(stub(), stub());

    });

    describe('->__invoke()', function () {

        it('should return a new Dispatcher', function () {

            $test = ($this->factory)(['middleware'], 'handler');

            expect($test)->toBeAnInstanceOf(Dispatcher::class);

        });

    });

});
