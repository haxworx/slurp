<?php

declare(strict_types=1);

/*
 * This file is part of the slurp package.
 * (c) Al Poole <netstar@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Repository;

use App\Entity\RobotLaunches;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RobotLaunches>
 *
 * @method RobotLaunches|null find($id, $lockMode = null, $lockVersion = null)
 * @method RobotLaunches|null findOneBy(array $criteria, array $orderBy = null)
 * @method RobotLaunches[]    findAll()
 * @method RobotLaunches[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RobotLaunchesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RobotLaunches::class);
    }

    public function findOneById($launchId)
    {
        return $this->createQueryBuilder('c')
            ->where('c.id = :launchId')
            ->setParameter('launchId', $launchId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findLastLaunchByBotId(int $botId)
    {
        return $this->createQueryBuilder('c')
            ->where('c.botId = :botId')
            ->setParameter('botId', $botId)
            ->orderBy('c.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
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
            ->from('robot_launches')
            ->where('bot_id = :botId')
            ->setParameter('botId', $botId)
            ->andWhere('DATE(start_time) = :date')
            ->setParameter('date', $date)
            ->executeQuery()
            ->fetchOne()
        ;
    }

    public function save(RobotLaunches $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(RobotLaunches $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAllByBotId($botId)
    {
        return $this->createQueryBuilder('c')
            ->where('c.botId = :botId')
            ->setParameter('botId', $botId)
            ->orderBy('c.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;
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
