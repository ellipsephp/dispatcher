<?php

use Psr\Http\Message\ServerRequestInterface;

use Interop\Http\ServerMiddleware\DelegateInterface;

use Ellipse\Dispatcher\FinalDelegate;
use Ellipse\Dispatcher\Exceptions\NoResponseReturnedException;

describe('FinalDelegate', function () {

    beforeEach(function () {

        $this->delegate = new FinalDelegate;

    });

    it('should implements DelegateInterface', function () {

        expect($this->delegate)->to->be->an->instanceof(DelegateInterface::class);

    });

    describe('->process()', function () {

        it('should fail', function () {

            $request = Mockery::mock(ServerRequestInterface::class);

            expect([$this->delegate, 'process'])->with($request)
                ->to->throw(NoResponseReturnedException::class);

        });

    });

});
