<?php

namespace JingdongLdopBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use JingdongLdopBundle\Entity\LogisticsDetail;

/**
 * @extends ServiceEntityRepository<LogisticsDetail>
 */
class LogisticsDetailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LogisticsDetail::class);
    }
}
