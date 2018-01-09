<?php

use Ellipse\Dispatcher\Exceptions\DispatcherExceptionInterface;
use Ellipse\Dispatcher\Exceptions\ResponseTypeException;

describe('ResponseTypeException', function () {

    beforeEach(function () {

        $this->exception = new ResponseTypeException('invalid');

    });

    it('should extend TypeError', function () {

        expect($this->exception)->toBeAnInstanceOf(TypeError::class);

    });

    it('should implement DispatcherExceptionInterface', function () {

        expect($this->exception)->toBeAnInstanceOf(DispatcherExceptionInterface::class);

    });

});
