<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Post;
use App\Entity\User;
use App\Entity\Comment;

class PostController extends AbstractController
{
    private $manager;
    private $repository;

    public function __construct(EntityManagerInterface $manager, ValidatorInterface $validator) {
        $this->manager    = $manager;
        $this->repository = $manager->getRepository(Post::class);
        $this->validator  = $validator;
    }

    /**
     * @Route("/posts", name="get-posts")
     */
   public function getPosts(Request $request): JsonResponse {
      $users = $this->repository->findAll();
      return new JsonResponse($users, Response::HTTP_OK);
   }
   /**
     * @Route("/post", name="create-post", methods={"POST"})
     */
    public function createPost(Request $request) {
        $data = json_decode($request->getContent(), true);

        $constraints = new Assert\Collection([
            'users_id' => [new Assert\NotBlank()],
            
        ]);

        $errors = $this->validator->validate($data, $constraints);
        if (count($errors) > 0) return new Response($errors);

        $users_id = $data['users_id'];
        

        $users_repository = $this->manager->getRepository(User::class);
        $users = $users_repository->findOneBy(['id' => $users_id]);

        
        $post = new Post();
        $post
            ->setUsers($users);

        
        
        $this->manager->persist($post);
        $this->manager->flush();

        return new JsonResponse(['status' => 'post added!'], Response::HTTP_OK);
    }

     /**
     * @Route("/post/{id}", name="update-post", methods={"PUT"})
     */
    public function updatePost(Request $request, $id) {
        $data = json_decode($request->getContent(), true);

        $constraints = new Assert\Collection([
            'users' => [new Assert\NotBlank()],
            'comments' => [new Assert\NotBlank()],
            'users_id' => [new Assert\NotBlank()],
            
        ]);

        $errors = $this->validator->validate($data, $constraints);
        if (count($errors) > 0) return new Response($errors);


        $users  = $data['users'];
        $comments   = $data['comments'];
        $users_id   = $data['users_id'];
       

        $users_repository = $this->manager->getRepository(User::class);
        $users = $users_repository->findOneBy(['id' => $users_id]);

        
        $post = $this->repository->findOneBy(['id' => $id]);
        $post
            ->setUsers($users)
            ->getComments($comments);
           

            $errors = $this->validator->validate($post);

            if (count($errors) > 0) {
                $errorsString = (string) $errors;
                return new Response($errorsString);
            }

        $this->manager->persist($post);
        $this->manager->flush();

        return new JsonResponse(['status' => 'Post updated!'], Response::HTTP_OK);
    }

       /**
     * @Route("/post/{id}", name="delete-post", methods={"DELETE"})
     */
    public function deletePost($id): JsonResponse
    {
        $post = $this->repository->findOneBy(['id' => $id]);
        $this->manager->remove($post);
        $this->manager->flush();
        return new JsonResponse(['status' => 'Post deleted!'], Response::HTTP_OK);
    }

}