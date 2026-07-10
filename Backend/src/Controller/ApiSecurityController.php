<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use DateTimeImmutable;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Http\Attribute\IsGranted;



#[Route('/api', name: 'app_api_')]
final class ApiSecurityController extends AbstractController
{
    public function __construct(

        private UserPasswordHasherInterface $hasher,

        private EntityManagerInterface $entityManager
    ) {}
    #[Route('/register', name: 'register', methods: ['POST'])]
    #[OA\Post(
    summary: "Créer un utilisateur",
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["email", "password", "pseudo"],
            properties: [
                new OA\Property(property: "email", type: "string", example: "test@mail.com"),
                new OA\Property(property: "password", type: "string", example: "123456"),
                new OA\Property(property: "pseudo", type: "string", example: "John Doe"),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: "Utilisateur créé"
        ),
        new OA\Response(
            response: 400,
            description: "Erreur de validation"
        )
    ]
    )]
    public function register(Request $request): JsonResponse
    {
            $data = json_decode($request->getContent(), true);

            if (!$data) {
                return new JsonResponse(['error' => 'JSON invalide'], Response::HTTP_BAD_REQUEST);
            }

            if (empty($data['email']) || empty($data['password'])) {
                return new JsonResponse(['error' => 'Champs obligatoires manquants'], Response::HTTP_BAD_REQUEST);
            }
            $user = new User();
            $user->setEmail($data['email']);
            $hashedPassword = $this->hasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);
            $user->setPseudo($data['pseudo'] ?? null);
            $user->setApiToken(bin2hex(random_bytes(32)));
            if (isset($data['isConducteur'])) {
                $user->setIsConducteur((bool)$data['isConducteur'] ?? false);
            }
            $user->setIsConducteur((bool) ($data['isConducteur'] ?? false));
            $user->setIsPassager((bool) ($data['isPassager'] ?? true));
            $user->setCreatedAt(new \DateTimeImmutable());
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            return new JsonResponse([
                'message' => 'Utilisateur inscrit avec succès !',
                'user' => [
                    'email' => $user->getEmail(),
                    'pseudo' => $user->getPseudo(),
                    'isConducteur' => $user->isConducteur(),
                    'isPassager' => $user->isPassager()
                ]
            ], Response::HTTP_CREATED);
        
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    #[OA\Post(
        summary: "Connexion de l'utilisateur",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "password"],
                properties: [
                    new OA\Property(property: "email", type: "string", example: "john@example.com"),
                    new OA\Property(property: "password", type: "string", example: "secret")
                ]
            )
        )
    )]
    public function login(#[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return new JsonResponse(['error' => 'identifiants incorrects'], Response::HTTP_UNAUTHORIZED);
        }
        return new JsonResponse(['user' => $user->getUserIdentifier(), 'roles' => $user->getRoles(), 'token' => $user->getApiToken()]);
    }
    #[Route('/logout', name: 'logout', methods: ['POST'])]
    #[OA\Post(
        summary: "Déconnexion de l'utilisateur",
        responses: [
            new OA\Response(
                response: 200,
                description: "Déconnexion réussie"
            )
        ]
    )]
    public function logout(): JsonResponse
    {
        $response = new JsonResponse(['message' => 'Déconnexion réussie']);
        $response->headers->clearCookie('token');
        return $response;
    }
    #[IsGranted('ROLE_USER')]
    #[Route('/user', name: 'current_user', methods: ['GET'])]
    #[OA\Get(
        summary: "Récupération des informations de l'utilisateur connecté",
        responses: [
            new OA\Response(
                response: 200,
                description: "Informations de l'utilisateur"
            )
        ]
    )]
    public function getCurrentUser(#[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse([
            'id' => $user->getId(),
            'roles' => $user->getRoles(),
            'token' => $user->getApiToken(),
            'pseudo' => $user->getPseudo(),
            'email' => $user->getEmail(),
            'nom' => $user->getNom(),
            'prenom' => $user->getPrenom(),
            'dateNaissance' => $user->getDateNaissance() ? $user->getDateNaissance()->format('Y-m-d') : null,
            'telephone' => $user->getTelephone(),
            'createdAt' => $user->getCreatedAt(),
        ]);
    }
    #[Route('/user/update', name: 'update_user', methods: ['PUT'])]
    #[OA\Put(
        summary: "Mise à jour des informations de l'utilisateur",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "pseudo", type: "string", example: "john_doe"),
                    new OA\Property(property: "email", type: "string", example: "john@example.com"),
                    new OA\Property(property: "isConducteur", type: "boolean", example: true),
                    new OA\Property(property: "isPassager", type: "boolean", example: true),
                ]
            )
        )
    )]
    public function updateUser(Request $request, #[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $data = json_decode($request->getContent(), true);

            if (empty($data)) {
                return new JsonResponse(['error' => 'Aucune donnée reçue'], Response::HTTP_BAD_REQUEST);
            }

            // --- Traitement chirurgical avec array_key_exists ---
            if (array_key_exists('pseudo', $data)) {
                $user->setPseudo($data['pseudo']);
            }
            if (array_key_exists('email', $data)) {
                $user->setEmail($data['email']);
            }
            if (array_key_exists('nom', $data)) {
                $user->setNom(!empty($data['nom']) ? $data['nom'] : null);
            }
            if (array_key_exists('prenom', $data)) {
                $user->setPrenom(!empty($data['prenom']) ? $data['prenom'] : null);
            }
            if (array_key_exists('telephone', $data)) {
                $user->setTelephone(!empty($data['telephone']) ? (int)$data['telephone'] : null);
            }
            if (array_key_exists('photo', $data)) {
                $user->setPhoto($data['photo']);
            }
            if (array_key_exists('credit', $data)) {
                $user->setCredits(!empty($data['credit']) ? (int)$data['credit'] : null);
            }
            if (array_key_exists('note', $data)) {
                $user->setNote(!empty($data['note']) ? (int)$data['note'] : null);
            }

            // --- Gestion sécurisée unique de la date de naissance ---
            if (array_key_exists('dateNaissance', $data)) {
                $dateRaw = $data['dateNaissance'];
                if (!empty($dateRaw)) {
                    // CORRECTION : On utilise \DateTime au lieu de \DateTimeImmutable
                    $user->setDateNaissance(new \DateTime($dateRaw)); 
                } else {
                    $user->setDateNaissance(null);
                }
            }

            // --- Gestion des statuts et rôles de l'application ---
            if (array_key_exists('isPassager', $data)) {
                $user->setIsPassager((bool)$data['isPassager']);
            }

            if (array_key_exists('isConducteur', $data)) {
                $isConducteur = (bool)$data['isConducteur'];
                $user->setIsConducteur($isConducteur);

                $currentRoles = $user->getRoles();
                if ($isConducteur) {
                    if (!in_array('ROLE_CONDUCTEUR', $currentRoles)) {
                        $currentRoles[] = 'ROLE_CONDUCTEUR';
                    }
                } else {
                    $currentRoles = array_diff($currentRoles, ['ROLE_CONDUCTEUR']);
                }
                $user->setRoles(array_values($currentRoles));
            }

            // Sauvegarde en base de données
            $user->setUpdatedAt(new \DateTimeImmutable());
            $this->entityManager->flush();

            // --- RETOUR DE L'OBJET MIS À JOUR ---
            return new JsonResponse([
                'message' => 'Utilisateur mis à jour avec succès',
                'user' => [
                    'id' => $user->getId(),
                    'roles' => $user->getRoles(),
                    'token' => $user->getApiToken(),
                    'pseudo' => $user->getPseudo(),
                    'email' => $user->getEmail(),
                    'nom' => $user->getNom(),
                    'prenom' => $user->getPrenom(),
                    'dateNaissance' => $user->getDateNaissance() ? $user->getDateNaissance()->format('Y-m-d') : null,
                    'telephone' => $user->getTelephone(),
                    'isConducteur' => $user->isConducteur(),
                    'isPassager' => $user->isPassager()
                ]
            ], Response::HTTP_OK);

        } catch (\Throwable $e) {
            return new JsonResponse([
                'error' => 'Erreur interne lors de la mise à jour',
                'details' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}