<?php

use Ellipse\Resolvers\RecursiveResolver;
use Ellipse\Resolvers\CallableWrapper;

use Ellipse\Stack\DefaultResolver;

describe('DefaultResolver', function () {

    beforeEach(function () {

        $this->resolver = new DefaultResolver;

    });

    it('should extends RecursiveResolver', function () {

        expect($this->resolver)->to->be->an->instanceof(RecursiveResolver::class);

    });

    describe('->resolve()', function () {

        it('should resolve callable objects as CallableWrapper instances', function () {

            $test = $this->resolver->resolve(function () {});

            expect($test)->to->be->an->instanceof(CallableWrapper::class);

        });

    });

});
