<?php

use Ellipse\Dispatcher\Exceptions\DispatcherExceptionInterface;
use Ellipse\Dispatcher\Exceptions\ContainerTypeException;

describe('ContainerTypeException', function () {

    it('should implement DispatcherExceptionInterface', function () {

        $test = new ContainerTypeException('invalid');

        expect($test)->toBeAnInstanceOf(DispatcherExceptionInterface::class);

    });

});
