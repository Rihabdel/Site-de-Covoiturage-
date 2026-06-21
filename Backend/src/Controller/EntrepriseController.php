<?php

namespace App\Controller;
use App\Entity\Entreprise;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use OpenApi\Attributes as OA;


#[Route('/entreprise', name: 'app_entreprise_')]
final class EntrepriseController extends AbstractController
{
    #[Route('/create', name: 'create', methods: ['POST'])]
    public function creerEntreprise(EntityManagerInterface $em): Response
    {
        $entreprise = new Entreprise();
        $entreprise->setNom('Ecoride');
        $entreprise->setAdresse('110 Rue de la Mobilité, 75000 Paris');
        $entreprise->setPresentation('présentation de l\'entreprise.');
        $entreprise->setEmail('ecoride@mail.com');

        $em->persist($entreprise);
        $em->flush();

    return $this->json([
        'message' => 'Entreprise créée avec succès',
        'id' => $entreprise->getId()
    ]);
    }
    #[Route('/{id}', name: 'afficher', methods: ['GET'])]
    #[OA\Get(
        tags: ["Entreprise"],
        summary: "Afficher les détails d'une entreprise",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de l'entreprise à afficher",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Détails de l'entreprise"
            ),
            new OA\Response(
                response: 404,
                description: "Entreprise non trouvable"
            )
        ]
    )]
    #[IsGranted("ROLE_ADMIN")]
    public function afficherEntreprise(int $id,EntityManagerInterface $em): JsonResponse
    {
        $entreprise = $em->getRepository(Entreprise::class)->find($id);

        if (!$entreprise) {
            return $this->json([
                'message' => 'Entreprise non trouvable'
            ], 404);
        }

        return $this->json([
            'nom' => $entreprise->getNom(),
            'adresse' => $entreprise->getAdresse(),
            'presentation' => $entreprise->getPresentation(),
            'email' => $entreprise->getEmail(),
            'photo' => $entreprise->getPhoto()
        ]);
    }
    

    #[Route('/{id}/upload-photo', name: 'upload_photo', methods: ['POST'])]
    #[OA\Post(
        tags: ["Entreprise"],
        summary: "Uploader une image pour l'entreprise",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    type: "object",
                    properties: [
                        new OA\Property(
                            property: "photo",
                            type: "string",
                            format: "binary",
                            description: "Fichier image à uploader"
                        )
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Image uploadée avec succès"
            ),
            new OA\Response(
                response: 400,
                description: "Requête invalide ou format de fichier incorrect"
            ),
            new OA\Response(
                response: 404,
                description: "Entreprise non trouvable"
            ),
            new OA\Response(
                response: 500,
                description: "Erreur lors de l'upload de l'image"
            )
        ]
    )]
    #[IsGranted("ROLE_ADMIN")]
    public function uploadPhoto(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $entreprise = $em->getRepository(Entreprise::class)->find($id);

        if (!$entreprise) {
            return $this->json(['error' => 'Entreprise non trouvable'], 404);
        }

        $file = $request->files->get('photo');

        if (!$file) {
            return $this->json(['error' => 'Aucun fichier reçu'], 400);
        }
        

        
        // Limite de taille à 5 Mo
        if ($file->getSize() > 5 * 1024 * 1024) {
        return $this->json(['error' => 'Fichier trop volumineux (max 5 Mo)'], 400);
        }

        $extension = $file->guessExtension() ?: 'jpg';
        $fileName = bin2hex(random_bytes(16)) . '.' . $extension;
        if (!in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/webp'],$extension)) {
            return $this->json(['error' => 'Format de fichier incorrect (JPEG, PNG, WEBP uniquement)'], 400);
        }
        try {
            // Déplace le fichier vers le répertoire de destination
            $file->move(
                $this->getParameter('entreprises_pictures_directory'),
                $fileName
            );

            if ($entreprise->getPhoto()) {
                $oldPath = rtrim($this->getParameter('entreprises_pictures_directory'), '/')
                    . '/' . $entreprise->getPhoto();

                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            $entreprise->setPhoto($fileName);
            $em->flush();
        } catch (FileException $e) {
            return $this->json(['error' => 'Erreur lors de l\'upload de l\'image'], 500);
        }

        return $this->json([
            'message' => 'Image enregistrée avec succès',
            'photo' => $fileName,
            'url' => '/uploads/entreprises/'.$fileName
        ]);
    }

    public function deleteEntreprise(int $id, EntityManagerInterface $em): JsonResponse
    {
        $entreprise = $em->getRepository(Entreprise::class)->find($id);

        if (!$entreprise) {
            return $this->json(['error' => 'Entreprise non trouvable'], 404);
        }
        try {
        if ($entreprise->getPhoto()) {
            $oldPath = rtrim($this->getParameter('entreprises_pictures_directory'), '/')
                . '/' . $entreprise->getPhoto();

            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        $em->remove($entreprise);
        $em->flush();

        return $this->json(['message' => 'Entreprise supprimée avec succès']);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Erreur lors de la suppression de l\'entreprise'], 500);
        }
    }
}