<?php

namespace App\Repository;

use App\Entity\RobotData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RobotData::class);
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
