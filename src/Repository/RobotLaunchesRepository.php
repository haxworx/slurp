<?php

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

    public function findOneByLaunchId($launchId)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.id = :launchId')
            ->setParameter('launchId', $launchId)
            ->getQuery()
            ->getOneOrNullResult();
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
            ->getResult();
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
