<?php

use function Eloquent\Phony\Kahlan\mock;

use Ellipse\Dispatcher\Exceptions\DispatcherExceptionInterface;
use Ellipse\Dispatcher\Exceptions\MiddlewareTypeException;

describe('MiddlewareTypeException', function () {

    beforeEach(function () {

        $this->exception = new MiddlewareTypeException('invalid', mock(TypeError::class)->get());

    });

    it('should extend TypeError', function () {

        expect($this->exception)->toBeAnInstanceOf(TypeError::class);

    });

    it('should implement DispatcherExceptionInterface', function () {

        expect($this->exception)->toBeAnInstanceOf(DispatcherExceptionInterface::class);

    });

});
