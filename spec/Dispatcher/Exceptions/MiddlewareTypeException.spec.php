<?php

use function Eloquent\Phony\Kahlan\mock;

use Ellipse\Dispatcher\Exceptions\DispatcherExceptionInterface;
use Ellipse\Dispatcher\Exceptions\MiddlewareTypeException;

describe('MiddlewareTypeException', function () {

    it('should implement DispatcherExceptionInterface', function () {

        $test = new MiddlewareTypeException('invalid', mock(TypeError::class)->get());

        expect($test)->toBeAnInstanceOf(DispatcherExceptionInterface::class);

    });

});
