<?php

use function Eloquent\Phony\Kahlan\mock;

use Ellipse\Dispatcher\Exceptions\DispatcherExceptionInterface;
use Ellipse\Dispatcher\Exceptions\DispatcherCreationException;

describe('DispatcherCreationException', function () {

    it('should implement DispatcherExceptionInterface', function () {

        $test = new DispatcherCreationException(mock(TypeError::class)->get());

        expect($test)->toBeAnInstanceOf(DispatcherExceptionInterface::class);

    });

});
