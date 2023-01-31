<?php

namespace App\Repository;

use App\Entity\RobotData;
use App\Entity\RobotSettings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @extends ServiceEntityRepository<RobotData>
 *
 * @method RobotData|null find($id, $lockMode = null, $lockVersion = null)
 * @method RobotData|null findOneBy(array $criteria, array $orderBy = null)
 * @method RobotData[]    findAll()
 * @method RobotData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RobotDataRepository extends ServiceEntityRepository
{
    public const PAGINATOR_PER_PAGE = 5;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RobotData::class);
        $this->doctrine = $registry;
    }

    public function getSearchPaginator(string $searchTerm, int $offset, int $userId): Paginator
    {
        $ids = $this->doctrine->getRepository(RobotSettings::class)->findAllBotIdsByUserId($userId);

        $query = $this->createQueryBuilder('c')
            ->where('c.data LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->andWhere('c.botId IN (:ids)')
            ->setParameter('ids', $ids)
            ->setMaxResults(self::PAGINATOR_PER_PAGE)
            ->setFirstResult($offset)
            ->getQuery()
        ;

        return new Paginator($query);
    }

    public function getPaginator(int $launchId, int $offset): Paginator
    {
        $query = $this->createQueryBuilder('c')
            ->where('c.launchId = :launchId')
            ->setParameter('launchId', $launchId)
            ->setMaxResults(self::PAGINATOR_PER_PAGE)
            ->setFirstResult($offset)
            ->getQuery()
        ;

        return new Paginator($query);
    }

    public function getCountByBotId(int $botId): int
    {
        return $this->createQueryBuilder('c')
            ->select('count(c.id)')
            ->where('c.botId = :botId')
            ->setParameter('botId', $botId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getByteCountByBotId(int $botId): int
    {
        return $this->createQueryBuilder('c')
            ->select('sum(c.length)')
            ->where('c.botId = :botId')
            ->setParameter('botId', $botId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findRecordIdByLaunchIdAndPath(int $launchId, string $path): ?int
    {
        $record = $this->createQueryBuilder('c')
            ->where('c.launchId = :launchId')
            ->setParameter('launchId', $launchId)
            ->andWhere('c.path = :path')
            ->setParameter('path', $path)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $record?->getId();
    }

    public function save(RobotData $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(RobotData $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function deleteAllByBotId(int $botId): void
    {
        $this->createQueryBuilder('c')
            ->delete()
            ->where('c.botId = :botId')
            ->setParameter('botId', $botId)
            ->getQuery()
            ->execute();
    }
}
