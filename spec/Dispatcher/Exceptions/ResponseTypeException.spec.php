<?php

use Ellipse\Dispatcher\Exceptions\DispatcherExceptionInterface;
use Ellipse\Dispatcher\Exceptions\ResponseTypeException;

describe('ResponseTypeException', function () {

    it('should implement DispatcherExceptionInterface', function () {

        $test = new ResponseTypeException('invalid');

        expect($test)->toBeAnInstanceOf(DispatcherExceptionInterface::class);

    });

});
