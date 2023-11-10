<?php

namespace App\Repository;

use App\Entity\Investors;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Investors>
 *
 * @method Investors|null find($id, $lockMode = null, $lockVersion = null)
 * @method Investors|null findOneBy(array $criteria, array $orderBy = null)
 * @method Investors[]    findAll()
 * @method Investors[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvestorsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Investors::class);
    }

//    /**
//     * @return Investors[] Returns an array of Investors objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('i.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Investors
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
