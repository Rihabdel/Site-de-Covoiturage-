<?php

namespace App\Controller;
use App\Entity\Covoiturage;
use App\Entity\User;
use App\Enum\Statut;
use Appp\Entity\trajet;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\CovoiturageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Attribute\Groups;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\SerializerInterface;


#[Route('api/covoiturage', name: 'app_api_covoiturage')]
final class CovoiturageController extends AbstractController
{
    public function __construct(private CovoiturageRepository $covoiturageRepository)
    {
    }
// afficher tous les covoiturages
    #[Route('/', name: 'index', methods: ['GET'])]
    #[OA\Get(
        tags: ["Covoiturage"],
        summary: "Récupérer la liste des covoiturages",
        parameters: [
            new OA\Parameter(
                name: "date",
                in: "query",
                description: "Filtrer par date (format: YYYY-MM-DD)",
                required: false,
                schema: new OA\Schema(type: "string", format: "date")
            ),
            new OA\Parameter(
                name: "heure",
                in: "query",
                description: "Filtrer par heure (format: HH:mm:ss)",
                required: false,
                schema: new OA\Schema(type: "string", format: "time")
            ),
            new OA\Parameter(
                name: "prixMax",
                in: "query",
                description: "Filtrer par prix maximum",
                required: false,
                schema: new OA\Schema(type: "number", format: "float")
            ),
            new OA\Parameter(
                name: "dureeMax",
                in: "query",
                description: "Filtrer par durée maximum (format: HH:mm:ss)",
                required: false,
                schema: new OA\Schema(type: "string", format: "time")
            ),
            new OA\Parameter(
                name: "note",
                in: "query",
                description: "Filtrer par note de l'utilisateur (0 à 5)",
                required: false,
                schema: new OA\Schema(type: "number", format: "float")
            ),
            new OA\Parameter(
                name: "ecologique",
                in: "query",
                description: "Filtrer par covoiturage écologique (true ou false)",
                required: false,
                schema: new OA\Schema(type: "boolean")
            )
        ],
    )]

    #[OA\Response(
        response: 404,
        description: "Aucun covoiturage trouvé avec les filtres spécifiés"
    )]
    #[OA\Response(
        response: 500,
        description: "Erreur lors de la récupération des covoiturages"
    )]
    #[Groups(['covoiturage:read'])]
    public function index(CovoiturageRepository $repo, Request $request): Response
    {
        try {
            $date = $request->query->get('date');
            $heure = $request->query->get('heure');
            $prixMax = $request->query->get('prixMax');
            $dureeMax = $request->query->get('dureeMax');
            $note = $request->query->get('note');
            $ecologique = $request->query->get('ecologique');

            // 1. aucun filtre
            if (!$date && !$heure && !$prixMax && !$dureeMax && !$note && $ecologique === null) {
                $data = $repo->findAllWithAvailableSeats();
                return $this->json($data, 200, [], ['groups' => 'covoiturage:read']);
            }
            if ($note) {
                $covoiturages = $repo->findByMinDriverNote((float)$note);
                return $this->json($covoiturages, 200, [], ['groups' => 'covoiturage:read']);
            }
        
            // 2. filtres simples (version propre)
            $covoiturages = $repo->findByFilters([
                'date' => $date ? new \DateTime($date) : null,
                'heure' => $heure ? new \DateTime($heure) : null,
                'prixMax' => $prixMax ? (float)$prixMax : null,
                'dureeMax' => $dureeMax ? (int)$dureeMax : null,
                'ecologique' => $ecologique !== null ? filter_var($ecologique, FILTER_VALIDATE_BOOLEAN) : null,
            ]);
            return $this->json([
                'message' => count($covoiturages) > 0 ? 'Covoiturages trouvés' : 'Aucun covoiturage trouvé',
                'data' => $covoiturages
            ], 200);
        } catch (\Throwable $e) {
            return $this->json([
                'message' => 'Erreur serveur',
                'error' => $e->getMessage()
            ], 500);
        }
    }
// afficher un covoiturage par id
    #[Route('/detail/{id}', name: 'show', methods: ['GET'])]
    #[OA\Get(
        tags: ["Covoiturage"],
        summary: "Récupérer les détails d'un covoiturage par ID",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                description: "ID du covoiturage",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
    )]
    #[OA\Response(
        response: 404,
        description: "Covoiturage non trouvé"
    )]
    #[OA\Response(
        response: 500,
        description: "Erreur lors de la récupération du covoiturage"
    )]
    #[Groups(['covoiturage:read'])]
    public function show(int $id): Response
    {
        $covoiturage = $this->covoiturageRepository->find($id);
        if (!$covoiturage) {
            return $this->json(['message' => 'Covoiturage not found'], 404);
        }
        $duree = $this->covoiturageRepository->duree($covoiturage);
        $age = $covoiturage->getUser()->getDateNaissance()->diff(new \DateTime());
        return new JsonResponse([
            'user' => $covoiturage->getUser()->getPseudo(),
            'photo' => $covoiturage->getUser()->getPhoto(),
            'adresseDepart' => $covoiturage->getAdresseDepart(),
            'adresseArrivee' => $covoiturage->getAdresseArrivee(),
            'nombrePlace' => $covoiturage->getNombrePlace(),
            'vehicule' => $covoiturage->getVehicule()->getMarque() . ' ' . $covoiturage->getVehicule()->getModele().' ' . $covoiturage->getVehicule()->getCouleur(),
            'immatriculation' => $covoiturage->getVehicule()->getNumeroImmatriculation().' ' . $covoiturage->getVehicule()->getDateImmatriculation()->format('Y-m-d'),
            'energie' => $covoiturage->getVehicule()->getEnergie(),
            'age' => $age->y,
            'dateDepart' => $covoiturage->getDate()->format('Y-m-d'),
            'heureDepart' => $covoiturage->getHeure()->format('H:i:s'),
            'prix' => $covoiturage->getPrix(),
            'duree' => $duree->format('%h heures %i minutes'),
            'note' => $covoiturage->getNote(),
        ], 200, [], ['groups' => 'covoiturage:read']);
    }

    #[Route('/user/{user}', name: 'get_by_user', methods: ['GET'])]
    public function getCovoituragesByUser(User $user): Response
    {
        $covoiturages = $this->covoiturageRepository->findBy(['user' => $user]);
        return $this->json($covoiturages, 200, [], ['groups' => 'covoiturage:read']);
    }
// afficher les covoiturages par utilisateur connecté
    #[Route('/me', name: 'get_my_covoiturages', methods: ['GET'])]
    #[OA\Get(
        tags: ["Covoiturage"],
        summary: "Récupérer les covoiturages de l'utilisateur connecté",
    )]
    #[OA\Response(
        response: 401,
        description: "Utilisateur non authentifié"
    )]
    #[OA\Response(
        response: 500,
        description: "Erreur lors de la récupération des covoiturages"
    )]
    #[Groups(['covoiturage:read'])]
    public function getMyCovoiturages(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['message' => 'Unauthorized'], 401);
        }
        $covoiturages = $this->covoiturageRepository->findBy(['user' => $user]);
        return $this->json($covoiturages, 200, [], ['groups' => 'covoiturage:read']);
    }


// mettre à jour le statut d'un covoiturage
    #[Route('/{id}/statut', name: 'update_statut', methods: ['PATCH'])]
    #[OA\Patch(
        tags: ["Covoiturage"],
        summary: "Mettre à jour le statut d'un covoiturage",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                description: "ID du covoiturage",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            description: "Nouveau statut du covoiturage",
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: "statut",
                        type: "string",
                        enum: ["EN_ATTENTE", "CONFIRME", "ANNULE", "TERMINE"],
                        description: "Nouveau statut du covoiturage"
                    )
                ]
            )
        ),
    )]
    #[OA\Response(
        response: 200,
        description: "Statut mis à jour avec succès"
    )]
    #[OA\Response(
        response: 400,
        description: "Requête invalide (statut manquant ou invalide)"
    )]
    #[OA\Response(
        response: 403,
        description: "Seul le chauffeur peut modifier le statut"
    )]
    #[OA\Response(
        response: 404,
        description: "Covoiturage introuvable"
    )]
    #[OA\Response(
        response: 500,
        description: "Erreur lors de la mise à jour du statut"
    )]
    #[IsGranted('ROLE_CONDUCTEUR', message: 'Seul le chauffeur peut modifier le statut')]
    public function updateStatut(int $id,Request $request, #[CurrentUser] User $user,CovoiturageRepository $repo,EntityManagerInterface $em): JsonResponse {

        if (!$user) {
            return $this->json(['message' => 'Unauthorized'], 401);
        }

        $covoiturage = $repo->find($id);

        if (!$covoiturage) {
            return $this->json(['message' => 'Covoiturage introuvable'], 404);
        }

        //  SEUL LE CHAUFFEUR PEUT MODIFIER
        if ($covoiturage->getChauffeur() !== $user) {
            return $this->json(['message' => 'Seul le chauffeur peut modifier le statut'], 403);
        }

        $data = $request->toArray();

        // validation statuts autorisés
        $statut = $data['statut'] ?? null;
        if (!$statut) {
            return $this->json(['message' => 'Statut manquant'], 400);
        }
        try {
            $statutEnum = StatutCovoiturage::from($statut);
        } catch (\ValueError $e) {
            return $this->json(['message' => 'Statut invalide'], 400);
        }

        $covoiturage->setStatut($statutEnum);

        $em->flush();

        return $this->json([
            'message' => 'Statut mis à jour avec succès',
            'statut' => $statut
        ]);
    }

// supprimer un covoiturage par id
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[OA\Delete(
        tags: ["Covoiturage"],
        summary: "Supprimer un covoiturage par ID",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                description: "ID du covoiturage à supprimer",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
    )]
    #[OA\Response(
        response: 404,
        description: "Covoiturage introuvable"
    )]
    #[OA\Response(
        response: 500,
        description: "Erreur lors de la suppression du covoiturage"
    )]
    #[IsGranted('ROLE_USER', message: 'Seul le chauffeur peut supprimer le covoiturage')]
    public function delete(int $id): JsonResponse
    {
        $covoiturage = $this->covoiturageRepository->find($id);

        if (!$covoiturage) {
            return $this->json(['message' => 'Covoiturage introuvable'], 404);
        }

        $this->covoiturageRepository->remove($covoiturage, true);

        return $this->json(['message' => 'Covoiturage supprimé avec succès'], 200);
    }

// créer un covoiturage
    #[Route('/', name: 'create', methods: ['POST'])]
    #[OA\Post(
        tags: ["Covoiturage"],
        summary: "Créer un nouveau covoiturage",
        requestBody: new OA\RequestBody(
            description: "Données du covoiturage à créer",
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "dateDepart", type: "string", format: "date-time", description: "Date et heure de départ (format: YYYY-MM-DD HH:mm:ss)"),
                    new OA\Property(property: "dateArrivee", type: "string", format: "date-time", description: "Date et heure d'arrivée (format: YYYY-MM-DD HH:mm:ss)"),
                    new OA\Property(property: "prix", type: "number", format: "float", description: "Prix du covoiturage"),
                    new OA\Property(property: "placeDisponible", type: "integer", description: "Nombre de places disponibles"),
                    new OA\Property(property: "voyageEcologique", type: "boolean", description: "Indique si le covoiturage est écologique"),
                    new OA\Property(property: "latitudeDepart", type: "number", format: "float", description: "Latitude du point de départ"),
                    new OA\Property(property: "longitudeDepart", type: "number", format: "float", description: "Longitude du point de départ"),
                    new OA\Property(property: "latitudeArrivee", type: "number", format: "float", description: "Latitude du point d'arrivée"),
                    new OA\Property(property: "longitudeArrivee", type: "number", format: "float", description: "Longitude du point d'arrivée"),
                    new OA\Property(property: "distance", type: "number", format: "float", description: "Distance du trajet en kilomètres"),
                    new OA\Property(property: "duree", type: "integer", description: "Durée du trajet en minutes")
                ]
            )
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Covoiturage créé avec succès"
    )]
    #[OA\Response(
        response: 400,
        description: "Requête invalide (données manquantes ou invalides)"
    )]
    #[OA\Response(
        response: 403,
        description: "Seuls les conducteurs peuvent créer un covoiturage"
    )]
    #[OA\Response(
        response: 500,
        description: "Erreur lors de la création du covoiturage"
    )]
    #[IsGranted('ROLE_CONDUCTEUR', message: 'Seuls les conducteurs peuvent créer un covoiturage')]
    public function create(Request $request, #[CurrentUser] User $user, CovoiturageRepository $repo, EntityManagerInterface $em): JsonResponse
    {   
        if (!$user->getIsConducteur()) {
            return $this->json([
                'message' => 'Seuls les conducteurs peuvent créer un covoiturage'
            ], 403);
        }
        // décoder le contenu JSON de la requête en tableau associatif
        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json(['message' => 'Données invalides'], 400);
        }
        $requiredFields = [
            'dateDepart','dateArrivee','prix','placeDisponible','voyageEcologique','latitudeDepart','longitudeDepart','latitudeArrivee','longitudeArrivee','distance','duree'
        ];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return $this->json([
                    'message' => "Le champ $field est obligatoire"
                ], 400);
            }
        }
        //LE CONDUCTEUR DOIT AVOIR UN VEHICULE
        if (!$user->getVehicule()) {
            return $this->json([
                'message' => 'Vous devez avoir un véhicule pour créer un covoiturage'
            ], 400);
        }
        
        // Créer un nouvel objet Trajet et définir ses propriétés   
        $trajet = new Trajet();
        $trajet->setLatitudeDepart($data['latitudeDepart']);
        $trajet->setLongitudeDepart($data['longitudeDepart']);
        $trajet->setLatitudeArrivee($data['latitudeArrivee']);
        $trajet->setLongitudeArrivee($data['longitudeArrivee']);
        $trajet->setDistance($data['distance']);
        $trajet->setDuree($data['duree']);
        $em->persist($trajet);
        
        
        
        // Créer un nouvel objet Covoiturage et définir ses propriétés
        ;
        $dateDepart = new \DateTime($data['dateDepart']);
        $dateArrivee = new \DateTime($data['dateArrivee']);

        if ($dateArrivee <= $dateDepart) {
            return $this->json([
                'message' => 'La date d\'arrivée doit être postérieure à la date de départ'
            ], 400);
        }
        if ($data['placeDisponible'] < 1) {
            return $this->json([
                'message' => 'Le nombre de places doit être supérieur à 0'
            ], 400);
        }
        
        $covoiturageData = new Covoiturage();
        $covoiturageData->setChauffeur($user);
        $covoiturageData->setDateDepart($dateDepart);
        $covoiturageData->setDateArrivee($dateArrivee);
        $covoiturageData->setPrix((float)$data['prix']);
        $covoiturageData->setPlaceDisponible((int)$data['placeDisponible']);
        $covoiturageData->setVoyageEcologique((bool)$data['voyageEcologique']);
        $covoiturageData->setStatut(Statut::PENDING); // Définir le statut par défaut à PENDING
        $covoiturageData->setVehicule($user->getVehicule());
        $covoiturageData->setTrajet($trajet); // Associer le trajet au covoiturage
        $em->persist($covoiturageData);
        $em->flush();
    
        return $this->json([
            'message' => 'Covoiturage créé avec succès',
            'covoiturage' => $covoiturageData
        ], 201, [
            'Location' => $this->generateUrl(
                'app_api_covoiturage_show',
                ['id' => $covoiturageData->getId()]
            )
        ], [
            'groups' => 'covoiturage:read'
        ]);
}
}