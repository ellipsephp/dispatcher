<?php

use function Eloquent\Phony\Kahlan\mock;

use Ellipse\Dispatcher\Exceptions\DispatcherExceptionInterface;
use Ellipse\Dispatcher\Exceptions\DispatcherCreationException;

describe('DispatcherCreationException', function () {

    beforeEach(function () {

        $this->previous = mock(TypeError::class)->get();

        $this->exception = new DispatcherCreationException($this->previous);

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
