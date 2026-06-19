<?php

namespace App\Controller;
use App\Repository\AvisRepository;
use App\Entity\Avis;
use App\Entity\User;
use App\Entity\Covoiturage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use OpenApi\Attributes as OA;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



#[Route('/api/avis', name: 'app_api_avis_')]
final class AvisController extends AbstractController
{
    
    #[Route('', name: 'add', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    #[OA\POST(
        tags: ["Avis"],
        summary: 'Ajouter un avis',
        description: 'Permet à un utilisateur de laisser un avis sur une commande terminée.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                example: [
                    'orderId' => 123,
                    'rating' => 4,
                    'comment' => 'Produit de bonne qualité, livraison rapide.'
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Avis créé avec succès',
                content: new OA\JsonContent(
                    example: [
                        'id' => 1,
                        'rating' => 4,
                        'comment' => 'Produit de bonne qualité, livraison rapide.'
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Requête invalide',
                content: new OA\JsonContent(
                    example: ['message' => 'Commande non terminée']
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Accès interdit',
                content: new OA\JsonContent(
                    example: ['message' => 'Accès interdit']
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Commande introuvable',
                content: new OA\JsonContent(
                    example: ['message' => 'Commande introuvable']
                )
            )
        ]
    )]
    public function add(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$this->getUser()) {
            return new JsonResponse(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
        $data = json_decode($request->getContent(), true);
        $requiredFields = ['covoiturageId', 'note', 'commentaire'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return new JsonResponse(['message' => "Le champ '$field' est requis"], 400);
            }
        }
        // auteur de l'avis est l'utilisateur actuellement connecté et a participé au covoiturage
        $user = $this->getAuteur();
        $covoiturage = $entityManager->getRepository(Covoiturage::class)->find($data['covoiturageId']);
        if (!$covoiturage) {
            return new JsonResponse(['message' => 'Covoiturage introuvable'], 404);
        }
        if (!$covoiturage) {
            return new JsonResponse(['message' => 'Covoiturage introuvable'], 404);
        }
        if ($covoiturage->getChauffeur() !== $user && !$covoiturage->getPassagers()->contains($user)) {
            return new JsonResponse(['message' => 'Vous n\'êtes pas autorisé à laisser un avis pour ce covoiturage'], 403);
        }
        
        // VERIFIER SI L'AUTEUR Est un passager ou le chauffeur du covoiturage
        if ($covoiturage->getChauffeur() !== $this->getUser() && !$covoiturage->getPassagers()->contains($this->getUser())) {
            return new JsonResponse(['message' => 'Vous n\'êtes pas autorisé à laisser un avis pour ce covoiturage'], 403);
        }
        // vérifier si l'avis existe déjà pour ce covoiturage
        $existingAvis = $entityManager
            ->getRepository(Avis::class)
            ->findOneBy(['covoiturage' => $covoiturage]);
        if ($existingAvis) {
            return new JsonResponse(['message' => 'Avis déjà existant'], 400);
        }
        $avis = new Avis();
        $avis->setNote($data['note']);
        $avis->setComment($data['commentaire']);
        $avis->setCovoiturage($covoiturage);
        $avis->setIsValidated(false);
        $avis->setCreatedAt(new \DateTimeImmutable());
        $entityManager->persist($avis);
        $entityManager->flush();
        return new JsonResponse([
            'id' => $avis->getId(),
            'note' => $avis->getNote(),
            'commentaire' => $avis->getCommentaire(),
        ], Response::HTTP_CREATED);
    }

    #[Route('', name: 'show', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Get(
        tags: ["Avis"],
        summary: 'Afficher les avis validés',
        description: 'Permet à un administrateur de voir tous les avis validés.',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Liste des avis validés',
                content: new OA\JsonContent(
                    example: [
                        [
                            'id' => 1,
                            'note' => 4,
                            'commentaire' => 'Produit de bonne qualité, livraison rapide.',
                            'createdAt' => '2024-06-01 12:34:56'
                        ],
                        [
                            'id' => 2,
                            'note' => 5,
                            'commentaire' => 'Excellent service, je recommande !',
                            'createdAt' => '2024-06-02 14:20:00'
                        ]
                    ]
                )
            )
        ]
    )]
    public function show(EntityManagerInterface $entityManager): JsonResponse
    {
        $avi = $entityManager
            ->getRepository(Avis::class)
            ->findByValidatedAvis();
        $responseData = [];
    // afficher que les avis validés
        foreach ($avi as $avis) {
            $responseData[] = [
                'id' => $avis->getId(),
                'note' => $avis->getNote(),
                'commentaire' => $avis->getCommentaire(),
                'createdAt' => $avis->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }
        
        return $this->json([
            'message' => 'Liste des avis validés',
            'avis' => $responseData
        ], Response::HTTP_OK);
    }


    #[Route('/delete/{id}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Delete(
        tags: ["Avis"],
        summary: 'Supprimer un avis',
        description: 'Permet à un administrateur de supprimer un avis.',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID de l\'avis à supprimer',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Avis supprimé avec succès',
                content: new OA\JsonContent(
                    example: ['message' => 'Avis supprimé avec succès']
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Avis introuvable',
                content: new OA\JsonContent(
                    example: ['message' => 'Avis introuvable']
                )
            )
        ]
    )]
    public function delete(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $avis = $entityManager->getRepository(Avis::class)->find($id);
        if (!$avis) {
            return new JsonResponse(['message' => 'Avis introuvable'], 404);
        }
        $entityManager->remove($avis);
        $entityManager->flush();
        return new JsonResponse(['message' => 'Avis supprimé avec succès'], Response::HTTP_OK);
    }

    #[Route('/{id}/validate', name: 'validate', methods: ['PATCH'])]
    #[IsGranted('ROLE_EMPLOYEE')]
    #[OA\Patch(
        tags: ["Avis"],
        summary: 'Valider un avis',
        description: 'Permet à un employé de valider un avis en changeant son statut.',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID de l\'avis à valider',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Avis validé avec succès',
                content: new OA\JsonContent(
                    example: ['message' => 'Avis validé avec succès']
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Avis déjà validé ou autre erreur',
                content: new OA\JsonContent(
                    example: ['message' => 'Avis déjà validé']
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Avis introuvable',
                content: new OA\JsonContent(
                    example: ['message' => 'Avis introuvable']
                )
            )
        ]
    )]
    public function validateAvis(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $avis = $entityManager
            ->getRepository(Avis::class)
            ->find($id);

        if (!$avis) {
            return new JsonResponse(
                ['message' => 'Avis introuvable'],
                Response::HTTP_NOT_FOUND
            );
        }

        if ($avis->isValidated()) {
            return new JsonResponse(
                ['message' => 'Avis déjà validé'],
                Response::HTTP_BAD_REQUEST
            );
        }
        $avis->setIsValidated(true);
        $entityManager->flush();

        return new JsonResponse(
            ['message' => 'Avis validé avec succès'],
            Response::HTTP_OK
        );
    }
}

