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
            return new JsonResponse(['error' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }
        return new JsonResponse(['user' => $user->getEmail(), 'roles' => $user->getRoles()]);
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
            'email' => $user->getEmail(),
            'pseudo' => $user->getPseudo(),
            'roles' => $user->getRoles(),
            'isConducteur' => $user->isConducteur(),
            'isPassager' => $user->isPassager(),
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

            if (!$data) {
                return new JsonResponse(['error' => 'JSON invalide'], Response::HTTP_BAD_REQUEST);
            }

            if (isset($data['pseudo'])) {
                $user->setPseudo($data['pseudo']);
            }
            if (isset($data['email'])) {
                $user->setEmail($data['email']);
            }
            if (isset($data['isConducteur'])) {
                $user->setIsConducteur((bool)$data['isConducteur']);
            }
            if (isset($data['isPassager'])) {
                $user->setIsPassager((bool)$data['isPassager']);
            }
            if (isset($data['telephone'])) {
                $user->setTelephone($data['telephone']);
            }
            if (isset($data['dateNaissance'])) {
                $user->setDateNaissance(new \DateTimeImmutable($data['dateNaissance']));
            }
            if (isset($data['photo'])) {
                $user->setPhoto($data['photo']);
            }
            if (isset($data['credit'])) {
                $user->setCredit((float)$data['credit']);
            }
            if (isset($data['note'])) {
                $user->setNote((float)$data['note']);
            }

            $user->setUpdatedAt(new \DateTimeImmutable());
            $this->entityManager->flush();

            return new JsonResponse(['message' => 'Utilisateur mis à jour avec succès']);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors de la lecture des données JSON'], Response::HTTP_BAD_REQUEST);
        }
    }
    #[Route('/user/{email}', name: 'get_user_by_email', methods: ['GET'])]
    #[OA\Get(
        summary: "Récupération des informations d'un utilisateur par son email",
        parameters: [
            new OA\Parameter(
                name: "email",
                in: "path",
                required: true,
                description: "Email de l'utilisateur",
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Informations de l'utilisateur"
            ),
            new OA\Response(
                response: 404,
                description: "Utilisateur non trouvé"
            )
        ]
    )]
    #[IsGranted('ROLE_ADMIN')]
    public function getUserByEmail(string $email): JsonResponse
    {
        $user = $this->entityManager->getRepository(User::class)->findByEmail($email);
        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non trouvé'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'pseudo' => $user->getPseudo(),
            'roles' => $user->getRoles(),
            'isConducteur' => $user->isConducteur(),
            'isPassager' => $user->isPassager(),
        ]);
    }

}
