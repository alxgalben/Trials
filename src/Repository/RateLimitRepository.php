<?php

namespace App\Repository;

use App\Entity\RateLimit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RateLimit>
 *
 * @method RateLimit|null find($id, $lockMode = null, $lockVersion = null)
 * @method RateLimit|null findOneBy(array $criteria, array $orderBy = null)
 * @method RateLimit[]    findAll()
 * @method RateLimit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RateLimitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RateLimit::class);
    }

    public function add(RateLimit $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(RateLimit $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /*public function findRateLimitByIpAddress(string $ipAddress): ?RateLimit
    {
        return $this->findOneBy(['ipAddress' => $ipAddress]);
    }*/

//    /**
//     * @return RateLimit[] Returns an array of RateLimit objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?RateLimit
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
