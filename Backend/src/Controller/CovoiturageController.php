<?php

namespace App\Controller;
use App\Entity\Covoiturage;
use App\Entity\User;
use App\Repository\VehiculeRepository;
use App\Enum\Statut;
use App\Entity\trajet;
use App\Service\GeocodingService;
use App\Service\RoutingService;
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


#[Route('api/covoiturage', name: 'app_api_covoiturage_')]
final class CovoiturageController extends AbstractController
{
    public function __construct(private CovoiturageRepository $covoiturageRepository)
    {

    }
// afficher tous les covoiturages
    #[Route('/list', name: 'list', methods: ['GET'])]
    #[OA\Get(
        tags: ["Covoiturage"],
        summary: "Récupérer la liste des covoiturages",
        parameters: [
            new OA\Parameter(
                name: "depart",
                in: "query",
                description: "Adresse de départ (ex: Nice)",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "arrivee",
                in: "query",
                description: "Adresse d'arrivée (ex: Marseille)",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "date",
                in: "query",
                description: "Date du covoiturage (format: YYYY-MM-DD)",
                required: false,
                schema: new OA\Schema(type: "string", format: "date")
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
     
    public function list(Request $request, CovoiturageRepository $repo): Response
    {
    try {

        $depart = $request->query->get('depart');
        $arrivee = $request->query->get('arrivee');
        $date = $request->query->get('date');

        // Recherche obligatoire
        if (!$depart || !$arrivee || !$date) {
            return $this->json([]);
        }
        $depart = trim(explode(',', $depart)[0]);
        $arrivee = trim(explode(',', $arrivee)[0]);

        

        // 1 - Recherche départ / arrivée / date
        $covoiturages = $repo->findByFilters([
            'depart' => $depart,
            'arrivee' => $arrivee,
            'date' => new \DateTime($date)
        ]);


        // 2 - Garder uniquement les trajets disponibles
        $covoiturages = array_values(array_filter(
            $covoiturages,
            function ($covoiturage) {
                return in_array($covoiturage->getStatut(), [
                    Statut::PLANNED,
                    Statut::CONFIRMED
                ]);
            }
        ));


        // 3 - Filtres sur les résultats
        $prixMax = $request->query->get('prixMax');
        $dureeMax = $request->query->get('dureeMax');
        $note = $request->query->get('note');
        $ecologique = $request->query->get('ecologique');


        $covoiturages = array_values(array_filter(
            $covoiturages,
            function ($covoiturage) use (
                $prixMax,
                $dureeMax,
                $note,
                $ecologique
            ) {

                // Prix maximum
                if ($prixMax !== null 
                    && $covoiturage->getPrix() > (float)$prixMax) {
                    return false;
                }


                // Voyage écologique
                if ($ecologique !== null) {
                    if ($covoiturage->isVoyageEcologique() 
                        !== filter_var($ecologique, FILTER_VALIDATE_BOOLEAN)) {
                        return false;
                    }
                }


                // Note chauffeur
                if ($note 
                    && $note !== 'all' 
                    && $note !== 'Note minimum') {

                    if ($covoiturage->getChauffeur()->getNote() < (float)$note) {
                        return false;
                    }
                }


                return true;
            }
        ));


        return $this->json(
            $covoiturages,
            200,
            [],
            ['groups' => ['covoiturage:read']]
        );


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
    public function show(int $id): JsonResponse
    {
        $covoiturage = $this->covoiturageRepository->find($id);
        if (!$covoiturage) {
            return $this->json(['message' => 'Covoiturage not found'], 404);
        }
        
       $age = null;

        if ($covoiturage->getChauffeur()->getDateNaissance()) {
            $age = $covoiturage->getChauffeur()
                ->getDateNaissance()
                ->diff(new \DateTime())
                ->y;
        }
        if ($covoiturage->getDateDepart() && $covoiturage->getDateArrivee()) {
            $duree = $covoiturage->getDateDepart()->diff($covoiturage->getDateArrivee());
        } else {
            $duree = new \DateInterval('PT0H0M');
        }
        if ($covoiturage->getPlaceDisponible() <= 0) {
            $statut = $covoiturage->getStatut();
            if ($statut === Statut::PLANNED) {
                $covoiturage->setStatut(Statut::FULL);
            }
        }
        // S'il y a des passagers, afficher sinon afficher "Aucun passager"
        $passagers = $covoiturage->getPassagers();

        return new JsonResponse([
            'id'=> $covoiturage->getId(),
            'chauffeur' => $covoiturage->getChauffeur()->getNom() . ' ' . $covoiturage->getChauffeur()->getPrenom(),
            'pseudo' => $covoiturage->getChauffeur()->getPseudo(),
            'photo' => $covoiturage->getChauffeur()->getPhoto(),
            'adresseDepart' => $covoiturage->getAdresseDepart(),
            'adresseArrivee' => $covoiturage->getAdresseArrivee(),
            'nombrePlace' => $covoiturage->getPlaceDisponible(),
            'vehicule' => $covoiturage->getVehicule()->getMarque() . ' ' . $covoiturage->getVehicule()->getModele().' ' . $covoiturage->getVehicule()->getCouleur(),
            'immatriculation' => $covoiturage->getVehicule()->getNumeroImmatriculation().' ' . $covoiturage->getVehicule()->getDateImmatriculation()->format('Y-m-d'),
            'energie' => $covoiturage->isVoyageEcologique() ? 'Ecologique' : 'Non écologique',
            'age' => $age,
            'dateDepart' => $covoiturage->getDateDepart()->format('Y-m-d'),
            'heureDepart' => $covoiturage->getDateDepart()->format('H:i:s'),
            'heureArrivee' => $covoiturage->getDateArrivee()->format('H:i:s'),
            'prix' => $covoiturage->getPrix(),
            'passagers' => count($passagers) > 0 ? $passagers : 'Aucun passager',
            'duree' => $duree->format('%h heures %i minutes'),
            'note' => $covoiturage->getChauffeur()->getNote(),
        ], 200);
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
    public function getMyCovoiturages(#[CurrentUser] User $user, CovoiturageRepository $repo): Response
    {
        if (!$user) {
            return $this->json(['message' => 'Unauthorized'], 401);
        }
        // Récupérer les covoiturages de l'utilisateur connecté
        $data= $repo->findBy(['chauffeur' => $user]);
        if (!$data) {
            return $this->json(['message' => 'Aucun covoiturage trouvé pour cet utilisateur'], 404);
        }
        return $this->json([
            'data' => $data
        ], 200, [], ['groups' => 'covoiturage:read', 'trajet:read']);
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
        $statut = $data['statut'];
        if (!$statut) {
            return $this->json(['message' => 'Statut manquant'], 400);
        }
        
            $statutEnum = Statut::tryFrom($data['statut']);
            if (!$statutEnum) {
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
    #[IsGranted('ROLE_CONDUCTEUR', message: 'Seul le chauffeur peut supprimer le covoiturage')]
    public function delete(int $id ,EntityManagerInterface $entityManager): JsonResponse
    {
        $covoiturage = $this->covoiturageRepository->findOneBy(['id' => $id]);

        if (!$covoiturage) {
            return $this->json(['message' => 'Covoiturage introuvable'], 404);
        }
        $user = $this->getUser();
        if ($covoiturage->getChauffeur() !== $user) {
            return $this->json(['message' => 'Seul le chauffeur peut supprimer le covoiturage'], 403);
        }
        // Supprimer le covoiturage
        $entityManager->remove($covoiturage);
        $entityManager->flush();
        $user->setCredits(2);
        $entityManager->flush();

        return $this->json(['message' => 'Covoiturage supprimé avec succès'], 200);
    }

// créer un covoiturage
        #[Route('/add', name: 'create', methods: ['POST'])]
        #[OA\Post(
            tags: ["Covoiturage"],
            summary: "Créer un nouveau covoiturage",
            requestBody: new OA\RequestBody(
                description: "Données du covoiturage à créer",
                required: true,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "adresseDepart", type: "string", description: "Adresse de départ (ex: Nice)"),
                        new OA\Property(property: "adresseArrivee", type: "string", description: "Adresse d'arrivée (ex: Marseille)"),
                        new OA\Property(property: "dateDepart", type: "string", format: "date-time", description: "Date et heure de départ (format: YYYY-MM-DD HH:mm:ss)"),
                        new OA\Property(property: "dateArrivee", type: "string", format: "date-time", description: "Date et heure d'arrivée (format: YYYY-MM-DD HH:mm:ss)"),
                        new OA\Property(property: "prix", type: "number", format: "float", description: "Prix du covoiturage"),
                        new OA\Property(property: "placeDisponible", type: "integer", description: "Nombre de places disponibles"),
                        new OA\Property(property: "vehicule", type: "integer", description: "ID du véhicule utilisé pour le covoiturage"),
                        new OA\Property(property: "voyageEcologique", type: "boolean", description: "Indique si le covoiturage est écologique"),
                        new OA\Property(property: "latitudeDepart", type: "number", format: "float", description: "43.7102"),
                        new OA\Property(property: "longitudeDepart", type: "number", format: "float", description: "7.2620"),
                        new OA\Property(property: "latitudeArrivee", type: "number", format: "float", description: "43.2965"),
                        new OA\Property(property: "longitudeArrivee", type: "number", format: "float", description: "5.3698"),
                        new OA\Property(property: "distance", type: "number", format: "float", description: "199.8"),
                        new OA\Property(property: "duree", type: "integer", description: "150")
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
        #[Route('/add', name: 'create', methods: ['POST'])]
#[IsGranted('ROLE_CONDUCTEUR')]
public function create(Request $request, #[CurrentUser] User $user, EntityManagerInterface $em,
    GeocodingService $geocoder, RoutingService $routing, VehiculeRepository $vehiculeRepository): JsonResponse {

    if (!$user->isConducteur()) {
        return $this->json([
            'message' => 'Seuls les conducteurs peuvent créer un covoiturage'
        ], 403);
    }

    $data = json_decode($request->getContent(), true);

    if (!$data) {
        return $this->json([
            'message' => 'Données invalides'
        ], 400);
    }

    // Vérification champs obligatoires
    $required = [
        'adresseDepart',
        'adresseArrivee',
        'dateDepart',
        'prix',
        'placeDisponible',
        'vehicule'
    ];

    foreach ($required as $field) {

        if (!isset($data[$field]) || $data[$field] === '') {

            return $this->json([
                'message' => "Le champ $field est obligatoire"
            ],400);
        }
    }
    // Récupération id de vehicule choisi par l'utilisateur
   $vehicule = $vehiculeRepository->find($data['vehicule']);

        if (!$vehicule) {
            return $this->json([
                'message' => 'Véhicule introuvable'
            ], 404);
        }
        

    // Géocodage

    try {

        $depart = $geocoder->getCoordinates(
            $data['adresseDepart']
        );

        $arrivee = $geocoder->getCoordinates(
            $data['adresseArrivee']
        );

    } catch(\Exception $e){

        return $this->json([
            'message'=>'Impossible de trouver les coordonnées'
        ],400);

    }

    // Calcul route OSRM

    $route = $routing->calculateRoute(
        $depart['longitude'],
        $depart['latitude'],
        $arrivee['longitude'],
        $arrivee['latitude']
    );
    try {

        $dateDepart = new \DateTime(
            $data['dateDepart']
        );


    } catch(\Exception $e){

        return $this->json([
            'message'=>'Date départ invalide'
        ],400);
    }



    // Date arrivée automatique

    $dateArrivee = clone $dateDepart;

    $dateArrivee->modify(
        '+' . $route['duree'] . ' minutes'
    );



    // Vérification places

    if ((int)$data['placeDisponible'] < 1) {

        return $this->json([
            'message'=>'Nombre de places invalide'
        ],400);
    }



    // Vérification crédits

    if ($user->getCredits() < 2) {

        return $this->json([
            'message'=>'Vous devez avoir au moins 2 crédits'
        ],400);
    }


    $user->setCredits(
        $user->getCredits() - 2
    );



    // Création Trajet


    $trajet = new Trajet();


    $trajet->setAdresseDepart(
        $data['adresseDepart']
    );

    $trajet->setAdresseArrivee(
        $data['adresseArrivee']
    );


    $trajet->setLatitudeDepart(
        $depart['latitude']
    );

    $trajet->setLongitudeDepart(
        $depart['longitude']
    );


    $trajet->setLatitudeArrivee(
        $arrivee['latitude']
    );

    $trajet->setLongitudeArrivee(
        $arrivee['longitude']
    );


    $trajet->setDistance(
       $route['distance']

    );


    $trajet->setDuree(
        $route['duree']
    );



    // Création covoiturage


    $covoiturage = new Covoiturage();


    $covoiturage->setChauffeur($user);

    $covoiturage->setVehicule($vehicule);

    $covoiturage->setTrajet($trajet);


    $covoiturage->setAdresseDepart(
        $data['adresseDepart']
    );

    $covoiturage->setAdresseArrivee(
        $data['adresseArrivee']
    );


    $covoiturage->setDateDepart(
        $dateDepart
    );


    $covoiturage->setDateArrivee(
        $dateArrivee
    );


    $covoiturage->setPrix(
        (float)$data['prix']
    );


    $covoiturage->setPlaceDisponible(
        (int)$data['placeDisponible']
    );


    // écologique automatique

    $eco = in_array(
        strtolower($vehicule->getEnergie()),
        ['electrique','hybride']
    );


    $covoiturage->setVoyageEcologique($eco);


    $covoiturage->setStatut(
        Statut::PLANNED
    );



    $em->persist($trajet);
    $em->persist($covoiturage);
    $em->persist($user);

    $em->flush();



    return $this->json([

        'message'=>'Covoiturage créé avec succès',

        'data'=>[

            'id'=>$covoiturage->getId(),

            'distance'=>$trajet->getDistance(),

            'duree'=>$trajet->getDuree(),

            'dateDepart'=>$dateDepart->format('Y-m-d H:i'),

            'dateArrivee'=>$dateArrivee->format('Y-m-d H:i')

        ]

    ],201);

}
    #[Route('/participer/{id}', name: 'participer', methods: ['POST'])]
    #[OA\Post(
        tags: ["Covoiturage"],
        summary: "Participer à un covoiturage",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                description: "ID du covoiturage auquel participer",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
    )]
    #[OA\Response(
        response: 200,
        description: "Participation réussie"
    )]
    #[OA\Response(
        response: 400,
        description: "Requête invalide (covoiturage complet ou déjà participé)"
    )]
    #[OA\Response(
        response: 403,
        description: "Seuls les passagers peuvent participer à un covoiturage"
    )]
    #[OA\Response(
        response: 404,
        description: "Covoiturage introuvable"
    )]
    #[OA\Response(
        response: 500,
        description: "Erreur lors de la participation au covoiturage"
    )]
// Seuls les passagers peuvent participer à un covoiturage    
    #[IsGranted('ROLE_USER', message: 'Seuls les passagers peuvent participer à un covoiturage')]
    public function participer(int $id, #[CurrentUser] User $user, EntityManagerInterface $em): JsonResponse
    {   
        
        $covoiturage = $this->covoiturageRepository->find($id);

        if (!$covoiturage) {
            return $this->json(['message' => 'Covoiturage introuvable'], 404);
        }
        if (!$user->isPassager()) {
        return $this->json([
            'message' => 'Vous devez être inscrit comme passager.'
        ], 403);
        }
        // Vérifier si le covoiturage est complet
        if ($covoiturage->getPlaceDisponible() <= 0) {
            return $this->json(['message' => 'Covoiturage complet'], 400);
        }

        // Vérifier si l'utilisateur a déjà participé
        if ($covoiturage->getPassagers()->contains($user)) {
            return $this->json(['message' => 'Vous avez déjà participé à ce covoiturage'], 400);
        }
        
        if ($covoiturage->getStatut() !== Statut::PLANNED && $covoiturage->getStatut() !== Statut::CONFIRMED) {
            return $this->json(['message' => 'Vous ne pouvez pas participer à ce covoiturage car il n\'est pas planifié ou confirmé'], 400);
        }
        if ($covoiturage->getDateDepart() < new \DateTime()) {
            return $this->json(['message' => 'Vous ne pouvez pas participer à ce covoiturage car il est déjà passé'], 400);
        }
        if ($covoiturage->getChauffeur()->getId() === $user->getId()) {
            return $this->json(['message' => 'Vous ne pouvez pas participer à votre propre covoiturage'], 400);
        }
        if ($user->getCredits() < $covoiturage->getPrix()) {
            return $this->json(['message' => 'Vous n\'avez pas assez de crédits pour participer à ce covoiturage'], 400);
        }
        
        // Ajouter l'utilisateur aux passagers
        $covoiturage->addPassager($user);
        $covoiturage->setPlaceDisponible($covoiturage->getPlaceDisponible() - 1);
        
        if ($covoiturage->getPlaceDisponible() === 0) {
            $covoiturage->setStatut(Statut::FULL);
        }
        $user->setCredits($user->getCredits() - (int) $covoiturage->getPrix());
        $em->persist($user);
        $em->persist($covoiturage);
        $em->flush();

        return $this->json($covoiturage, 200, [], ['groups' => 'covoiturage:read']);
    }

}