<?php

namespace App\Repository;

use App\Entity\Tutelles;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tutelles>
 *
 * @method Tutelles|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tutelles|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tutelles[]    findAll()
 * @method Tutelles[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TutellesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tutelles::class);
    }

//    /**
//     * @return Tutelles[] Returns an array of Tutelles objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Tutelles
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
