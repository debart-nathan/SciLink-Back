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


    public function search($search, $additionalData, $offset = 0, $limit = 10)
    {
        $queryBuilder = $this->createQueryBuilder('i');

        $queryBuilder->where($queryBuilder->expr()->orX(
            $queryBuilder->expr()->like('LOWER(i.name)', ':search'),
            $queryBuilder->expr()->like('LOWER(i.sigle)', ':search')
        ))
            ->setParameter('search', '%' . strtolower($search) . '%');
        /* TODO Choisir si on veut domaine ou pas
        if (!empty($additionalData['domain'])) {
            $qb->join('i.domains', 'd')
                ->andWhere('d.id = :domainId')
                ->setParameter('domainId', $additionalData['domain']);
        }
        */

        $queryBuilder->setFirstResult($offset)
            ->setMaxResults($limit);
        return $queryBuilder->getQuery()->getResult();
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
