<?php

namespace App\Repository;

use App\Entity\ResearchCenters;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ResearchCenters>
 *
 * @method ResearchCenters|null find($id, $lockMode = null, $lockVersion = null)
 * @method ResearchCenters|null findOneBy(array $criteria, array $orderBy = null)
 * @method ResearchCenters[]    findAll()
 * @method ResearchCenters[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResearchCentersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResearchCenters::class);
    }

    public function search($search, $additionalData, $offset = 0, $limit = 10)
    {
        $queryBuilder = $this->createQueryBuilder('rc');

        if (!empty($search)) {
            $queryBuilder->andWhere('LOWER(rc.libelle) LIKE :search OR LOWER(rc.sigle) LIKE :search')
                ->setParameter('search', '%' . strtolower($search) . '%');
        }

        if (!empty($additionalData['is_active'])) {
            $is_active = $additionalData['is_active'] === 'true' ? 1 : 0;
            $queryBuilder->andWhere('rc.is_active = :isActive')
                ->setParameter('isActive', $is_active);
        }

        if (!empty($additionalData['domain'])) {
            $queryBuilder->innerJoin('rc.domains', 'd')
                ->andWhere('d.id = :domainId')
                ->setParameter('domainId', $additionalData['domain']);
        }


        if (!empty($additionalData['date_start'])) {
            $queryBuilder->andWhere('rc.founding_year >= :dateStart')
                ->setParameter('dateStart', $additionalData['date_start']);
        }

        if (!empty($additionalData['date_end'])) {
            $queryBuilder->andWhere('rc.founding_year <= :dateEnd')
                ->setParameter('dateEnd', $additionalData['date_end']);
        }

        $queryBuilder->setFirstResult($offset)
        ->setMaxResults($limit);

        return $queryBuilder->getQuery()->getResult();
    }

    //    /**
    //     * @return ResearchCenters[] Returns an array of ResearchCenters objects
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

    //    public function findOneBySomeField($value): ?ResearchCenters
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
