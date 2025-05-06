<?php

namespace JingdongLdopBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use JingdongLdopBundle\Entity\JdlConfig;

/**
 * @extends ServiceEntityRepository<JdlConfig>
 */
class JdlConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JdlConfig::class);
    }

    /**
     * 获取所有有效的配置
     *
     * @return JdlConfig[]
     */
    public function findAllValid(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.valid = true')
            ->getQuery()
            ->getResult();
    }

    public function getDefaultConfig(): ?JdlConfig
    {
        return $this->createQueryBuilder('c')
            ->where('c.valid = true')
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
