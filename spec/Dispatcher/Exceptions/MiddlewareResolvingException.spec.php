<?php

use Ellipse\Dispatcher\Exceptions\DispatcherExceptionInterface;
use Ellipse\Dispatcher\Exceptions\MiddlewareResolvingException;

describe('MiddlewareResolvingException', function () {

    it('should implement DispatcherExceptionInterface', function () {

        $test =new MiddlewareResolvingException('invalid');

        expect($test)->toBeAnInstanceOf(DispatcherExceptionInterface::class);

    });

});
