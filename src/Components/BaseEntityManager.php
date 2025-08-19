<?php declare(strict_types=1);

namespace Ngs\AmeriaPayment\Components;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;

/**
 * Class BaseEntityManager base class for all managers which initialize repository
 */
abstract class BaseEntityManager
{
    protected EntityRepositoryInterface $repository;

    /**
     * BaseEntityManager constructor.
     *
     * @param EntityRepositoryInterface $repository
     */
    public function __construct(EntityRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

}