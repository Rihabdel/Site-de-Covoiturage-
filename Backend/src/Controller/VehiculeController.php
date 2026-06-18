<?php
namespace App\Controller;

use App\Entity\Vehicule;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Attribute\Groups;

#[Route('/api/vehicule', name: 'app_api_')]
class VehiculeController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'list', methods: ['GET'])]
    #[OA\Get(
        tags: ["Vehicule"],
        summary: "Récupérer la liste des véhicules",
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste des véhicules récupérée"
            )
        ]
    )]
    public function getVehicules(#[CurrentUser] User $user): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_CONDUCTEUR', null, 'Only drivers can access this resource.');
            $vehicules = $this->entityManager->getRepository(Vehicule::class)->findByProprietaire($user);
        return new JsonResponse([
        'vehicules' => array_map(fn($v) => [
            'marque' => $v->getMarque(),
            'modele' => $v->getModele(),
            'couleur' => $v->getCouleur(),
            'energie' => $v->getEnergie(),
            'numeroImmatriculation' => $v->getNumeroImmatriculation(),
            'dateImmatriculation' => $v->getDateImmatriculation()?->format('Y-m-d'),
        ], $vehicules)
    ]);
    }
    #[Route('/', name: 'create', methods: ['POST'])]
    #[OA\Post(
        tags: ["Vehicule"],
        summary: "Créer un véhicule",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["marque", "modele", "couleur", "energie", "numeroImmatriculation", "dateImmatriculation"],
                properties: [
                    new OA\Property(property: "marque", type: "string", example: "Toyota"),
                    new OA\Property(property: "modele", type: "string", example: "Corolla"),
                    new OA\Property(property: "couleur", type: "string", example: "Rouge"),
                    new OA\Property(property: "energie", type: "string", example: "Essence"),
                    new OA\Property(property: "numeroImmatriculation", type: "string", example: "AB-123-CD"),
                    new OA\Property(property: "dateImmatriculation", type: "string", format: "date", example: "2020-01-01"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Véhicule créé"
            ),
            new OA\Response(
                response: 400,
                description: "Erreur de validation"
            )
        ]
    )]
    public function create(Request $request,#[CurrentUser] User $user): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_CONDUCTEUR');
        $data = $request->toArray();

        $vehicule = new Vehicule();
        $vehicule->setMarque($data['marque'] ?? null);
        $vehicule->setModele($data['modele'] ?? null);
        $vehicule->setCouleur($data['couleur'] ?? null);
        $vehicule->setEnergie($data['energie'] ?? null);

        $vehicule->setNumeroImmatriculation($data['numeroImmatriculation'] ?? null);

        if (!empty($data['dateImmatriculation'])) {
            $vehicule->setDateImmatriculation(new \DateTime($data['dateImmatriculation']));
        }

        $vehicule->setProprietaire($user);
        $this->entityManager->persist($vehicule);
        $this->entityManager->flush();

        return $this->json([
        'message' => 'Véhicule créé',
        'vehicule' => $vehicule
        ], Response::HTTP_CREATED, [], ['groups' => 'vehicule:read']);
    }
    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    #[OA\Put(
        tags: ["Vehicule"],
        summary: "Mettre à jour un véhicule",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID du véhicule à mettre à jour",
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "marque", type: "string", example: "Toyota"),
                    new OA\Property(property: "modele", type: "string", example: "Corolla"),
                    new OA\Property(property: "couleur", type: "string", example: "Rouge"),
                    new OA\Property(property: "energie", type: "string", example: "Essence"),
                    new OA\Property(property: "numeroImmatriculation", type: "string", example: "AB-123-CD"),
                    new OA\Property(property: "dateImmatriculation", type: "string", format: "date", example: "2020-01-01"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Véhicule mis à jour"
            ),
            new OA\Response(
                response: 400,
                description: "Erreur de validation"
            ),
            new OA\Response(
                response: 404,
                description: "Véhicule non trouvé"
            ),
            new OA\Response(
                response: 403,
                description: "Accès refusé"
            )
        ]
    )]
    public function update(Request $request,#[CurrentUser] User $user,int $id): JsonResponse {
        if (!$user) {
            return $this->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $vehicule = $this->entityManager->getRepository(Vehicule::class)->find($id);

        if (!$vehicule) {
            return $this->json(['message' => 'Véhicule not found'], Response::HTTP_NOT_FOUND);
        }

        if ($vehicule->getProprietaire() !== $user) {
            return $this->json(['message' => 'You are not the owner of this vehicle'], Response::HTTP_FORBIDDEN);
        }

        $data = $request->toArray();

        $vehicule->setMarque($data['marque'] ?? $vehicule->getMarque());
        $vehicule->setModele($data['modele'] ?? $vehicule->getModele());
        $vehicule->setCouleur($data['couleur'] ?? $vehicule->getCouleur());
        $vehicule->setEnergie($data['energie'] ?? $vehicule->getEnergie());
        if (!empty($data['dateImmatriculation'])) {
            $vehicule->setDateImmatriculation(new \DateTime($data['dateImmatriculation']));
        }
        if (!empty($data['numeroImmatriculation'])) {
            $vehicule->setNumeroImmatriculation($data['numeroImmatriculation']);
        }
        $this->entityManager->flush();
        return $this->json([
            'message' => 'Véhicule mis à jour',
            'vehicule' => $vehicule
        ], Response::HTTP_OK, [], ['groups' => 'vehicule:read']);
    }
    
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[OA\Delete(
        tags: ["Vehicule"],
        summary: "Supprimer un véhicule",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID du véhicule à supprimer",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Véhicule supprimé"
            ),
            new OA\Response(
                response: 404,
                description: "Véhicule non trouvé"
            ),
            new OA\Response(
                response: 403,
                description: "Accès refusé"
            )
        ]
    )]
    public function delete(#[CurrentUser] User $user, int $id): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_CONDUCTEUR', null, 'Only drivers can access this resource.');
        $vehicule = $this->entityManager->getRepository(Vehicule::class)->find($id);

        if (!$vehicule) {
            return $this->json(['message' => 'Véhicule not found'], Response::HTTP_NOT_FOUND);
        }

        if ($vehicule->getProprietaire() !== $user) {
            return $this->json(['message' => 'You are not the owner of this vehicle'], Response::HTTP_FORBIDDEN);
        }

        $this->entityManager->remove($vehicule);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Véhicule supprimé',
            'vehicule' => $vehicule
        ], Response::HTTP_OK, [], ['groups' => 'vehicule:read']);
    }
}