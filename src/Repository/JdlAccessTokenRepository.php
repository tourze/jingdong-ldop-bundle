<?php

namespace JingdongLdopBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use JingdongLdopBundle\Entity\JdlAccessToken;

/**
 * @extends ServiceEntityRepository<JdlAccessToken>
 */
class JdlAccessTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JdlAccessToken::class);
    }
}
