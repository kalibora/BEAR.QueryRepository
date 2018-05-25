<?php
/**
 * This file is part of the BEAR.QueryRepository package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\QueryRepository;

use BEAR\RepositoryModule\Annotation\Cacheable;
use BEAR\RepositoryModule\Annotation\Purge;
use BEAR\RepositoryModule\Annotation\Refresh;
use Ray\Di\AbstractModule;

class QueryRepositoryAopModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        // @Cacheable
        $this->bindPriorityInterceptor(
            $this->matcher->annotatedWith(Cacheable::class),
            $this->matcher->startsWith('onGet'),
            [CacheInterceptor::class]
        );
        foreach (['onPost', 'onPut', 'onPatch', 'onDelete'] as $starts) {
            $this->bindInterceptor(
                $this->matcher->annotatedWith(Cacheable::class),
                $this->matcher->startsWith($starts),
                [CommandInterceptor::class]
            );
            $this->bindInterceptor(
                $this->matcher->logicalNot(
                    $this->matcher->annotatedWith(Cacheable::class)
                ),
                $this->matcher->logicalOr(
                    $this->matcher->annotatedWith(Purge::class),
                    $this->matcher->annotatedWith(Refresh::class)
                ),
                [RefreshInterceptor::class]
            );
        }
    }
}
