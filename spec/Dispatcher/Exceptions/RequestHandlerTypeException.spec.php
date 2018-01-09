<?php

use function Eloquent\Phony\Kahlan\mock;

use Ellipse\Dispatcher\Exceptions\DispatcherExceptionInterface;
use Ellipse\Dispatcher\Exceptions\RequestHandlerTypeException;

describe('RequestHandlerTypeException', function () {

    it('should implement DispatcherExceptionInterface', function () {

        $test = new RequestHandlerTypeException('invalid', mock(TypeError::class)->get());

        expect($test)->toBeAnInstanceOf(DispatcherExceptionInterface::class);

    });

});
