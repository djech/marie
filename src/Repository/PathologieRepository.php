<?php

namespace App\Repository;

use App\Entity\Pathologie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Pathologie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Pathologie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Pathologie[]    findAll()
 * @method Pathologie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PathologieRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Pathologie::class);
    }

//    /**
//     * @return Pathologie[] Returns an array of Pathologie objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Pathologie
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
