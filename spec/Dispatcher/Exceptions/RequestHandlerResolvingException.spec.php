<?php

use Ellipse\Dispatcher\Exceptions\DispatcherExceptionInterface;
use Ellipse\Dispatcher\Exceptions\RequestHandlerResolvingException;

describe('RequestHandlerResolvingException', function () {

    it('should implement DispatcherExceptionInterface', function () {

        $test = new RequestHandlerResolvingException('invalid');

        expect($test)->toBeAnInstanceOf(DispatcherExceptionInterface::class);

    });

});
