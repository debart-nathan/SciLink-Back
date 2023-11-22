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

    private function getQueryBuilder($search, $additionalData, $qb)
    {
        $qb->where($qb->expr()->orX(
            $qb->expr()->like('LOWER(i.name)', ':search'),
            $qb->expr()->like('LOWER(i.sigle)', ':search')
        ))
            ->setParameter('search', '%' . strtolower($search) . '%');
        /* TODO V2 Choisir si on veut domaine ou pas
        if (!empty($additionalData['domain'])) {
            $qb->join('i.domains', 'd')
                ->andWhere('d.id = :domainId')
                ->setParameter('domainId', $additionalData['domain']);
        }
        */

       // Add more conditions based on $additionalData as needed
        return $qb;
    }

    public function search($search, $additionalData, $offset = 0, $limit = 10)
    {
        $queryBuilder = $this->createQueryBuilder('i');

        $queryBuilder = $this->getQueryBuilder($search, $additionalData, $queryBuilder);

        $queryBuilder->setFirstResult($offset)
            ->setMaxResults($limit);
        return $queryBuilder->getQuery()->getResult();
    }
    public function getTotalCount($search, $additionalData)
    {
        // Utilisez la même logique de recherche, mais sans limit et offset
        $qb = $this->createQueryBuilder('i');

        // Ajoutez ici vos conditions de recherche, par exemple
        $qb = $this->getQueryBuilder($search, $additionalData, $qb);

        // Retourne le nombre total d'éléments correspondant à la recherche
        return (int) $qb->select('COUNT(i.id)')
            ->getQuery()
            ->getSingleScalarResult();
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
