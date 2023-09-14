<?php

namespace App\Repository;

use App\Entity\ShortTimeToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ShortTimeToken>
 *
 * @method ShortTimeToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShortTimeToken|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShortTimeToken[]    findAll()
 * @method ShortTimeToken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShortTimeTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShortTimeToken::class);
    }

    public function add(ShortTimeToken $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ShortTimeToken $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return ShortTimeToken[] Returns an array of ShortTimeToken objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ShortTimeToken
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
