<?php

namespace App\Repository;

use App\Entity\Manages;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Manages>
 *
 * @method Manages|null find($id, $lockMode = null, $lockVersion = null)
 * @method Manages|null findOneBy(array $criteria, array $orderBy = null)
 * @method Manages[]    findAll()
 * @method Manages[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ManagesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Manages::class);
    }

//    /**
//     * @return Manages[] Returns an array of Manages objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Manages
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
