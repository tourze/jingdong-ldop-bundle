<?php

declare(strict_types=1);

namespace JingdongLdopBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use JingdongLdopBundle\Entity\JdlConfig;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;

/**
 * @extends ServiceEntityRepository<JdlConfig>
 */
#[Autoconfigure(public: true)]
#[AsRepository(entityClass: JdlConfig::class)]
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
            ->getResult()
        ;
    }

    public function getDefaultConfig(): ?JdlConfig
    {
        return $this->createQueryBuilder('c')
            ->where('c.valid = true')
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function save(JdlConfig $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(JdlConfig $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
