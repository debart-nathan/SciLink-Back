<?php

namespace App\Repository;

use App\Entity\Researchers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Researchers>
 *
 * @method Researchers|null find($id, $lockMode = null, $lockVersion = null)
 * @method Researchers|null findOneBy(array $criteria, array $orderBy = null)
 * @method Researchers[]    findAll()
 * @method Researchers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResearchersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Researchers::class);
    }

//    /**
//     * @return Researchers[] Returns an array of Researchers objects
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

//    public function findOneBySomeField($value): ?Researchers
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
