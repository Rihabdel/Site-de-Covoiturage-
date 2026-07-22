<?php

namespace App\Repository;

use App\Entity\Covoiturage;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Covoiturage>
 */
class CovoiturageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Covoiturage::class);
    }
    public  function findAllWithAvailableSeats(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.placeDisponible > 0')
            ->getQuery()
            ->getResult();
    }
    public function findByFilters(array $filters): array
    {
        $qb = $this->createQueryBuilder('c');

        if (!empty($filters['prixMax'])) {
            $qb->andWhere('c.prix <= :prixMax')
            ->setParameter('prixMax', $filters['prixMax']);
        }

        if (!empty($filters['dureeMax'])) {
            $qb->andWhere('c.dateArrivee - c.dateDepart <= :dureeMax')
            ->setParameter('dureeMax', $filters['dureeMax']);
        }

        if (isset($filters['ecologique'])) {
            $qb->andWhere('c.voyageEcologique = :eco')
            ->setParameter('eco', $filters['ecologique']);
        }

        $qb->andWhere('c.placeDisponible > 0');
        return $qb->getQuery()->getResult();
    }
    public function findByMinDriverNote(float $note): array
    {
        return $this->createQueryBuilder('c')
            ->join('c.chauffeur', 'u')
            ->where('u.note >= :note')
            ->setParameter('note', $note)
            ->getQuery()
            ->getResult();
    }
    public function duree(Covoiturage $covoiturage): ?\DateInterval
    {
        $dateDepart = $covoiturage->getDateDepart();
        $dateArrivee = $covoiturage->getDateArrivee();

        if ($dateDepart && $dateArrivee) {
            return $dateDepart->diff($dateArrivee);
        }
        return null;
    }
    //change statut du covoiturage par le chauffeur
   public function findByUser(User $user): array
   {
       return $this->createQueryBuilder('c')
           ->where('c.chauffeur = :user')
           ->setParameter('user', $user)
           ->getQuery()
           ->getResult();
   }
   public function findParticipationsByUser(User $user): array
   {
       return $this->createQueryBuilder('c')
           ->join('c.passagers', 'p')
           ->where('p = :user')
           ->setParameter('user', $user)
           ->getQuery()
           ->getResult();
   }
}