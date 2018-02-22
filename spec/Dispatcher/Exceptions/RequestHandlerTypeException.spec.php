<?php

use function Eloquent\Phony\Kahlan\mock;

use Ellipse\Dispatcher\Exceptions\DispatcherExceptionInterface;
use Ellipse\Dispatcher\Exceptions\RequestHandlerTypeException;

describe('RequestHandlerTypeException', function () {

    beforeEach(function () {

        $this->exception = new RequestHandlerTypeException('invalid');

    });

    it('should extend TypeError', function () {

        expect($this->exception)->toBeAnInstanceOf(TypeError::class);

    });

    it('should implement DispatcherExceptionInterface', function () {

        expect($this->exception)->toBeAnInstanceOf(DispatcherExceptionInterface::class);

    });

});
