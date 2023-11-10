<?php

namespace App\Repository;

use App\Entity\Domaines;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Domaines>
 *
 * @method Domaines|null find($id, $lockMode = null, $lockVersion = null)
 * @method Domaines|null findOneBy(array $criteria, array $orderBy = null)
 * @method Domaines[]    findAll()
 * @method Domaines[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DomainesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Domaines::class);
    }

//    /**
//     * @return Domaines[] Returns an array of Domaines objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Domaines
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
