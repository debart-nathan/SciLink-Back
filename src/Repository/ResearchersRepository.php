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

    private function getQueryBuilder($search, $additionalData, $qb)
    {
        if ($search) {
            $qb->join('r.app_user', 'u')
                ->where('LOWER(u.user_name) LIKE :search')
                ->orWhere('LOWER(u.first_name) LIKE :search')
                ->orWhere('LOWER(u.last_name) LIKE :search')
                ->setParameter('search', '%' . strtolower($search) . '%');
        }

        if (!empty($additionalData['domain'])) {
            $qb->innerJoin('r.domains', 'd')
                ->andWhere('d.id = :domainId')
                ->setParameter('domainId', $additionalData['domain']);
        }
        return $qb;
    }
    public function search($search, $additionalData, $offset = 0, $limit = 10)
    {
        $queryBuilder = $this->createQueryBuilder('r');
        $queryBuilder = $this->getQueryBuilder($search, $additionalData, $queryBuilder);

        $queryBuilder->setFirstResult($offset)
            ->setMaxResults($limit);

        return $queryBuilder->getQuery()->getResult();
    }
    public function getTotalCount($search,  $additionalData,)
    {
        // Utilisez la même logique de recherche, mais sans limit et offset
        $qb = $this->createQueryBuilder('r');


        $qb = $this->getQueryBuilder($search, $additionalData, $qb);

        // Retourne le nombre total d'éléments correspondant à la recherche
        return (int) $qb->select('COUNT(DISTINCT r.id)')
            ->getQuery()
            ->getSingleScalarResult();
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
