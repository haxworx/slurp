<?php

namespace App\Repository;

use App\Entity\RobotLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RobotLog>
 *
 * @method RobotLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method RobotLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method RobotLog[]    findAll()
 * @method RobotLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RobotLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RobotLog::class);
    }

    public function save(RobotLog $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(RobotLog $entity, bool $flush = false): void
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
