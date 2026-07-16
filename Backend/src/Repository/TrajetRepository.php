<?php

namespace App\Repository;

use App\Entity\Trajet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Trajet>
 */
class TrajetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trajet::class);
    }
public function recherche(
    string $depart,
    string $arrivee,
    \DateTimeInterface $date
)
{
    return $this->createQueryBuilder('c')
        ->join('c.trajet', 't')

        ->andWhere('t.villeDepart LIKE :depart')
        ->andWhere('t.villeArrivee LIKE :arrivee')

        ->setParameter('depart', '%'.$depart.'%')
        ->setParameter('arrivee', '%'.$arrivee.'%')

        ->getQuery()
        ->getResult();
}
}