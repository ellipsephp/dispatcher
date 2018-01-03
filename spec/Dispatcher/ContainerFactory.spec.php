<?php

use function Eloquent\Phony\Kahlan\stub;
use function Eloquent\Phony\Kahlan\mock;

use Psr\Container\ContainerInterface;

use Psr\Http\Message\ServerRequestInterface;

use Ellipse\Dispatcher\ContainerFactory;
use Ellipse\Dispatcher\Exceptions\ContainerTypeException;

describe('ContainerFactory', function () {

    beforeEach(function () {

        $this->delegate = stub();

        $this->factory = new ContainerFactory($this->delegate);

    });

    describe('->__invoke()', function () {

        beforeEach(function () {

            $this->request = mock(ServerRequestInterface::class)->get();

        });

        context('when the delegate returns an implementation of ContainerInterface', function () {

            it('should proxy the delegate', function () {

                $container = mock(ContainerInterface::class)->get();

                $this->delegate->with($this->request)->returns($container);

                $test = ($this->factory)($this->request);

                expect($test)->toBe($container);

            });

        });

        context('when the delegate does not return an implementation of ContainerInterface', function () {

            it('should throw a ContainerTypeException', function () {

                $this->delegate->returns('container');

                $test = function () {

                    ($this->factory)($this->request);

                };

                $exception = new ContainerTypeException('container');

                expect($test)->toThrow($exception);

            });

        });

    });

});
