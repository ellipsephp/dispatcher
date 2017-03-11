<?php

use Ellipse\Resolvers\AbstractResolver;
use Ellipse\Dispatcher\VoidResolver;

describe('VoidResolver', function () {

    beforeEach(function () {

        $this->resolver = new VoidResolver;

    });

    it('should extends AbstractResolver', function () {

        expect($this->resolver)->to->be->an->instanceof(AbstractResolver::class);

    });

    describe('->canResolve()', function () {

        it('should return false', function () {

            $test = $this->resolver->canResolve('test');

            expect($test)->to->be->false();

        });

    });

});
