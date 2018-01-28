<?php

use function Eloquent\Phony\Kahlan\mock;

use Ellipse\Dispatcher\Exceptions\DispatcherExceptionInterface;
use Ellipse\Dispatcher\Exceptions\MiddlewareTypeException;

describe('MiddlewareTypeException', function () {

    beforeEach(function () {

        $this->previous = mock(TypeError::class)->get();

        $this->exception = new MiddlewareTypeException('invalid', $this->previous);

    });

    it('should extend TypeError', function () {

        expect($this->exception)->toBeAnInstanceOf(TypeError::class);

    });

    it('should implement DispatcherExceptionInterface', function () {

        expect($this->exception)->toBeAnInstanceOf(DispatcherExceptionInterface::class);

    });

    describe('->getPrevious()', function () {

        it('should return the previous exception', function () {

            $test = $this->exception->getPrevious();

            expect($test)->toBe($this->previous);

        });

    });

});
