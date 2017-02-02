<?php

use Psr\Http\Message\ServerRequestInterface;

use Interop\Http\ServerMiddleware\DelegateInterface;

use Ellipse\Stack\FinalDelegate;
use Ellipse\Contracts\Stack\Exceptions\NoResponseReturnedException;

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

            $test = function ($request) {

                $this->delegate->process($request);

            };

            expect($test)->with($request)->to->throw(NoResponseReturnedException::class);

        });

    });

});
