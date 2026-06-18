<?php

namespace App\Repository;

use App\Entity\Vehicule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vehicule>
 */
class VehiculeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vehicule::class);
    }
    public function findByProprietaire($proprietaire)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.proprietaire = :proprietaire')
            ->setParameter('proprietaire', $proprietaire)
            ->getQuery()
            ->getResult();
    }
}
