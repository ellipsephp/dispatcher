<?php

use Ellipse\Dispatcher\Exceptions\DispatcherExceptionInterface;
use Ellipse\Dispatcher\Exceptions\MiddlewareStackExhaustedException;

describe('MiddlewareStackExhaustedException', function () {

    it('should implement DispatcherExceptionInterface', function () {

        $test = new MiddlewareStackExhaustedException;

        expect($test)->toBeAnInstanceOf(DispatcherExceptionInterface::class);

    });

});
