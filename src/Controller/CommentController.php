<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;

class CommentController extends AbstractController
{
    private $manager;
    private $repository;
    private $validator;
    
    public function __construct(EntityManagerInterface $manager, ValidatorInterface $validator) {
        $this->manager    = $manager;
        $this->repository = $manager->getRepository(Comment::class);
        $this->validator  = $validator;
    }

    /**
     * @Route("/comments", name="get-comments")
     */
   public function getComments(Request $request): JsonResponse {
      $comments = $this->repository->findAll();
      return new JsonResponse($comments, Response::HTTP_OK);
   }

    /**
     * @Route("/comment", name="create-comment", methods={"POST"})
     */
    public function createcomment(Request $request) {
        $data = json_decode($request->getContent(), true);

        $constraints = new Assert\Collection([
            'post_id' => [new Assert\NotBlank()],
            'user_id'=> [new Assert\NotBlank()],
            'content' => [new Assert\NotBlank()],
        ]);

        $errors = $this->validator->validate($data, $constraints);
        if (count($errors) > 0) return new Response($errors);

        $post_id    = $data['post_id'];
        $user_id    = $data['user_id'];
        $content    = $data['content'];

        $post_repository = $this->manager->getRepository(Post::class);
        $post = $post_repository->findOneBy(['id' => $post_id]);

        $user_repository = $this->manager->getRepository(User::class);
        $user = $user_repository->findOneBy(['id' => $user_id]);

        $comment = new Comment();
        $comment
            ->setPost($post)
            ->setUser($user)
            ->setContent($content);

        $this->manager->persist($comment);
        $this->manager->flush();

        return new JsonResponse(['status' => 'Comment added!'], Response::HTTP_OK);
    }

     /**
     * @Route("/comment/{id}", name="update-comment", methods={"PUT"})
     */
    public function updateComment(Request $request, $id) {
        $data = json_decode($request->getContent(), true);
        $constraints = new Assert\Collection([
            'post_id' => [new Assert\NotBlank()],
            'content' => [new Assert\NotBlank()],
        
        ]);

        $errors = $this->validator->validate($data, $constraints);
        if (count($errors) > 0) return new Response($errors);

        $post_id    = $data['post_id'];
        $content    = $data['content'];

        $post_repository = $this->manager->getRepository(Post::class);
        $post = $post_repository->findOneBy(['id' => $post_id]);

        $comment = $this->repository->findOneBy(['id' => $id]);
        $comment
        ->setPost($post)
        ->setContent($content);

        $this->manager->persist($comment);
        $this->manager->flush();

       
        return new JsonResponse(['status' => 'Comment updated!'], Response::HTTP_OK);
    }

      /**
     * @Route("/comment/{id}", name="delete-comment", methods={"DELETE"})
     */
    public function deleteComment($id): JsonResponse
    {
        $comment = $this->repository->findOneBy(['id' => $id]);
        $this->manager->remove($comment);
        $this->manager->flush();
        return new JsonResponse(['status' => 'Comment deleted!'], Response::HTTP_OK);
    }
}
