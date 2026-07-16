<?php

namespace App\Repository;

use App\Entity\Covoiturage;
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
        if (!empty($filters['depart'])) {
            
            $qb->andWhere('LOWER(c.adresseDepart) LIKE LOWER(:depart)')
            ->setParameter('depart', '%' . $filters['depart'] . '%');
        }

        if (!empty($filters['arrivee'])) {
            $qb->andWhere('LOWER(c.adresseArrivee) LIKE LOWER(:arrivee)')
            ->setParameter('arrivee', '%' . $filters['arrivee'] . '%');
        }

        if (!empty($filters['date'])) {

            $dateDebut = $filters['date'];

            if (!$dateDebut instanceof \DateTimeInterface) {
                $dateDebut = new \DateTime($dateDebut);
            }

            $dateFin = (clone $dateDebut)->modify('+1 day');

            $qb->andWhere('c.dateDepart >= :dateDebut')
            ->andWhere('c.dateDepart < :dateFin')
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('dateFin', $dateFin);
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
}