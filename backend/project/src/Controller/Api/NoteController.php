<?php
// src/Controller/Api/NoteController.php
namespace App\Controller\Api;

use App\Entity\Note;
use App\Repository\NoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/notes')]
class NoteController extends AbstractController
{
    public function __construct(
        private NoteRepository $noteRepository,
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {}

    /**
     * Получить список заметок
     */
    #[Route('', name: 'api_notes_index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);

        $notes = $this->noteRepository->findAllPaginated($page, $limit);

        $data = $this->serializer->serialize($notes, 'json', [
            'groups' => ['note:read'],
            'json_encode_options' => JSON_UNESCAPED_UNICODE,
        ]);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * Создать новую заметку
     */
    #[Route('', name: 'api_notes_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $note = $this->serializer->deserialize(
                $request->getContent(),
                Note::class,
                'json'
            );

            // Проверяем валидность данных
            $errors = $this->validator->validate($note);

            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[$error->getPropertyPath()] = $error->getMessage();
                }

                return $this->json([
                    'success' => false,
                    'errors' => $errorMessages
                ], Response::HTTP_BAD_REQUEST);
            }

            $this->entityManager->persist($note);
            $this->entityManager->flush();

            $data = $this->serializer->serialize($note, 'json', [
                'groups' => ['note:read'],
                'json_encode_options' => JSON_UNESCAPED_UNICODE,
            ]);

            return new JsonResponse($data, Response::HTTP_CREATED, [], true);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Invalid JSON data'
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Получить конкретную заметку
     */
    #[Route('/{id}', name: 'api_notes_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $note = $this->noteRepository->find($id);

        if (!$note) {
            return $this->json([
                'success' => false,
                'error' => 'Note not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $data = $this->serializer->serialize($note, 'json', [
            'groups' => ['note:read'],
            'json_encode_options' => JSON_UNESCAPED_UNICODE,
        ]);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * Обновить заметку
     */
    #[Route('/{id}', name: 'api_notes_update', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $note = $this->noteRepository->find($id);

        if (!$note) {
            return $this->json([
                'success' => false,
                'error' => 'Note not found'
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            $data = json_decode($request->getContent(), true);

            if (isset($data['title'])) {
                $note->setTitle($data['title']);
            }

            if (isset($data['content'])) {
                $note->setContent($data['content']);
            }

            // Проверяем валидность
            $errors = $this->validator->validate($note);

            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[$error->getPropertyPath()] = $error->getMessage();
                }

                return $this->json([
                    'success' => false,
                    'errors' => $errorMessages
                ], Response::HTTP_BAD_REQUEST);
            }

            $this->entityManager->flush();

            $data = $this->serializer->serialize($note, 'json', [
                'groups' => ['note:read'],
                'json_encode_options' => JSON_UNESCAPED_UNICODE,
            ]);

            return new JsonResponse($data, Response::HTTP_OK, [], true);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Invalid JSON data'
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Удалить заметку
     */
    #[Route('/{id}', name: 'api_notes_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $note = $this->noteRepository->find($id);

        if (!$note) {
            return $this->json([
                'success' => false,
                'error' => 'Note not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($note);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Note deleted successfully'
        ], Response::HTTP_OK);
    }
}
