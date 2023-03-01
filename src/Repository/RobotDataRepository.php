<?php

declare(strict_types=1);

/*
 * This file is part of the slurp package.
 * (c) Al Poole <netstar@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Repository;

use App\Entity\RobotData;
use App\Entity\RobotSettings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

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

    public function getSearchPaginator(string $searchTerm, int $offset, bool $newerFirst, int $userId): Paginator
    {
        $ids = $this->doctrine->getRepository(RobotSettings::class)->findAllBotIdsByUserId($userId);

        $query = $this->createQueryBuilder('c')
            ->where('c.data LIKE :searchTerm')
            ->setParameter('searchTerm', '%'.$searchTerm.'%')
            ->andWhere('c.botId IN (:ids)')
            ->setParameter('ids', $ids)
            ->andWhere('c.contentType LIKE \'text%\'')
        ;
        if ($newerFirst) {
            $query->orderBy('c.id', 'DESC');
        } else {
            $query->orderBy('c.id', 'ASC');
        }
        $query->setMaxResults(self::PAGINATOR_PER_PAGE)
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
            ->getSingleScalarResult()
        ;
    }

    public function getCountByBotIdAndDate(int $botId, string $date): int
    {
        $conn = $this->getEntityManager()
            ->getConnection()
        ;
        $queryBuilder = $conn->createQueryBuilder();

        return $queryBuilder
            ->select('count(id)')
            ->from('robot_data')
            ->where('bot_id = :botId')
            ->setParameter('botId', $botId)
            ->andWhere('DATE(time_stamp) = :date')
            ->setParameter('date', $date)
            ->executeQuery()
            ->fetchOne()
        ;
    }

    public function getImageCountByBotIdAndDate(int $botId, string $date): int
    {
        $conn = $this->getEntityManager()
            ->getConnection()
        ;
        $queryBuilder = $conn->createQueryBuilder();

        return $queryBuilder
            ->select('count(id)')
            ->from('robot_data')
            ->where('bot_id = :botId')
            ->setParameter('botId', $botId)
            ->andWhere('DATE(time_stamp) = :date')
            ->setParameter('date', $date)
            ->andWhere('content_type LIKE \'%image%\'')
            ->executeQuery()
            ->fetchOne()
        ;
    }

    public function getByteCountByBotId(int $botId): int
    {
        return $this->createQueryBuilder('c')
            ->select('sum(c.length)')
            ->where('c.botId = :botId')
            ->setParameter('botId', $botId)
            ->getQuery()
            ->getSingleScalarResult()
        ;
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
            ->getOneOrNullResult()
        ;

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
            ->execute()
        ;
    }
}
