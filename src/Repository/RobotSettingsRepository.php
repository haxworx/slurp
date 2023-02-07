<?php

namespace App\Repository;

use App\Entity\RobotSettings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RobotSettings>
 *
 * @method RobotSettings|null find($id, $lockMode = null, $lockVersion = null)
 * @method RobotSettings|null findOneBy(array $criteria, array $orderBy = null)
 * @method RobotSettings[]    findAll()
 * @method RobotSettings[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RobotSettingsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RobotSettings::class);
    }

    public function save(RobotSettings $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function countByUserId(int $userId): int
    {
        return $this->createQueryBuilder('c')
            ->select('count(c.userId)')
            ->where('c.userId = :id')
            ->setParameter('id', $userId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function userOwnsBot(int $userId, int $botId): bool
    {
        return (bool) $this->createQueryBuilder('c')
            ->where('c.userId = :userId')
            ->setParameter('userId', $userId)
            ->andWhere('c.id = :botId')
            ->setParameter('botId', $botId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAllBotIdsByUserId($userId): array
    {
        $result = $this->createQueryBuilder('c')
            ->select('c.id')
            ->where('c.userId = :id')
            ->setParameter('id', $userId)
            ->groupBy('c.id')
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_SCALAR_COLUMN);

        return $result;
    }

    public function domainExists(RobotSettings $settings, int $userId): bool
    {
        $n = $this->createQueryBuilder('c')
            ->select('count(c.userId)')
            ->where('c.userId = :id')
            ->setParameter('id', $userId)
            ->andWhere('c.domainName = :domain')
            ->setParameter('domain', $settings->getDomainName())
            ->andWhere('c.scheme = :scheme')
            ->setParameter('scheme', $settings->getScheme())
            ->getQuery()
            ->getSingleScalarResult();

        return $n > 0 ? true : false;
    }

    public function isSameEntity(RobotSettings $settings, int $userId): bool
    {
        return (bool) $this->createQueryBuilder('c')
            ->where('c.userId = :id')
            ->setParameter('id', $userId)
            ->andWhere('c.domainName = :domain')
            ->setParameter('domain', $settings->getDomainName())
            ->andWhere('c.scheme = :scheme')
            ->setParameter('scheme', $settings->getScheme())
            ->andWhere('c.id = :botId')
            ->setParameter('botId', $settings->getId())
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function remove(RobotSettings $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAllByUserId(int $userId): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.userId = :id')
            ->setParameter('id', $userId)
            ->getQuery()
            ->getResult();
    }

    public function findOneByUserIdAndBotId(int $userId, int $botId)
    {
        return $this->createQueryBuilder('c')
            ->where('c.userId = :userId')
            ->setParameter('userId', $userId)
            ->andWhere('c.id = :botId')
            ->setParameter('botId', $botId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function deleteAllByUserId(int $userId): void
    {
        $this->createQueryBuilder('c')
            ->delete()
            ->where('c.userId = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->execute();
    }
}
