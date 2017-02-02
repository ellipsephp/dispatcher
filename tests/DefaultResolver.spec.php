<?php

use Ellipse\Contracts\Resolver\ResolverInterface;

use Ellipse\Stack\DefaultResolver;

describe('DefaultResolver', function () {

    beforeEach(function () {

        $this->delegate = new DefaultResolver;

    });

    it('should implements ResolverInterface', function () {

        expect($this->delegate)->to->be->an->instanceof(ResolverInterface::class);

    });

});
