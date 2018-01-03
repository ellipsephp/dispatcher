<?php

use Ellipse\Dispatcher\Exceptions\DispatcherExceptionInterface;
use Ellipse\Dispatcher\Exceptions\MiddlewareTypeException;

describe('MiddlewareTypeException', function () {

    it('should implement DispatcherExceptionInterface', function () {

        $test = new MiddlewareTypeException('invalid');

        expect($test)->toBeAnInstanceOf(DispatcherExceptionInterface::class);

    });

});
