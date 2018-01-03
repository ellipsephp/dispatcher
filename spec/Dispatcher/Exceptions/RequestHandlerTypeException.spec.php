<?php

use Ellipse\Dispatcher\Exceptions\DispatcherExceptionInterface;
use Ellipse\Dispatcher\Exceptions\RequestHandlerTypeException;

describe('RequestHandlerTypeException', function () {

    it('should implement DispatcherExceptionInterface', function () {

        $test = new RequestHandlerTypeException('invalid');

        expect($test)->toBeAnInstanceOf(DispatcherExceptionInterface::class);

    });

});
