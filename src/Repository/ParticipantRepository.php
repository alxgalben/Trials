<?php

namespace App\Repository;

use App\Entity\Participant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Participant>
 *
 * @method Participant|null find($id, $lockMode = null, $lockVersion = null)
 * @method Participant|null findOneBy(array $criteria, array $orderBy = null)
 * @method Participant[]    findAll()
 * @method Participant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParticipantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Participant::class);
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function countUserReceiptCountToday(\DateTimeInterface $date, string $email): int
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.email = :email')
            ->andWhere('p.submittedAt >= :startOfDay AND p.submittedAt <= :endOfDay')
            ->setParameter('email', $email)
            ->setParameter('startOfDay', $date->format('Y-m-d 00:00:00'))
            ->setParameter('endOfDay', $date->format('Y-m-d 23:59:59'))
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findByDateInterval(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.submittedAt >= :startDate')
            ->andWhere('p.submittedAt <= :endDate')
            /*->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)*/
            ->setParameters(['startDate' => $startDate, 'endDate' => $endDate]);

        return $qb->getQuery()->getResult();
    }

    public function findWinnersPerWeek():array
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.winner = true');

        return $qb->getQuery()->getResult();
    }

    public function findWinnersPerWeekByNumber(int $weekNumber): array
    {
        $startDate= new \DateTime();
        $endDate= new \DateTime();
        $startDate->setISODate(2023, $weekNumber, 1);
        $endDate->setISODate(2023, $weekNumber ,7);
        $startDate->setTime(0,0,0);
        $endDate->setTime(23,59,59);
        //dd($startDate);
        //dd($endDate);

        $qb = $this->createQueryBuilder('p')
            ->where('p.submittedAt >= :startDate')
            ->andWhere('p.submittedAt <= :endDate')
            ->andWhere('p.isWinner = true')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);
        //dump($qb->getQuery()->getResult());
        return $qb->getQuery()->getResult();
    }

    public function add(Participant $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Participant $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
