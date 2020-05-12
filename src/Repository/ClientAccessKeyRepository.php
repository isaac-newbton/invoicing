<?php

namespace App\Repository;

use App\Entity\ClientAccessKey;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ClientAccessKey|null find($id, $lockMode = null, $lockVersion = null)
 * @method ClientAccessKey|null findOneBy(array $criteria, array $orderBy = null)
 * @method ClientAccessKey[]    findAll()
 * @method ClientAccessKey[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClientAccessKeyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClientAccessKey::class);
    }

    // /**
    //  * @return ClientAccessKey[] Returns an array of ClientAccessKey objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ClientAccessKey
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
