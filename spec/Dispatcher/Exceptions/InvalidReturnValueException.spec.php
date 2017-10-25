<?php

use Ellipse\Dispatcher\Exceptions\DispatcherExceptionInterface;
use Ellipse\Dispatcher\Exceptions\InvalidReturnValueException;

describe('InvalidReturnValueException', function () {

    it('should implement DispatcherExceptionInterface', function () {

        $test =new InvalidReturnValueException('invalid');

        expect($test)->toBeAnInstanceOf(DispatcherExceptionInterface::class);

    });

});
