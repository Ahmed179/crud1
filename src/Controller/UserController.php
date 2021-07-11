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
use App\Entity\User;
use Exception;

/**
 * @Route("/user", name="user")
 */
class UserController extends AbstractController
{
    private $manager;
    private $repository;

    public function __construct(EntityManagerInterface $manager, ValidatorInterface $validator)
    {
        $this->manager = $manager;
        $this->repository = $manager->getRepository(User::class);
        $this->validator  = $validator;
    }

    /**
     * @Route("/signup", name="signup", methods={"POST"})
     */
    public function signUp(Request $request){
        $data = json_decode($request->getContent(), true);

        $constraints = new Assert\Collection([
            'email' => [new Assert\NotBlank()],
            'password' => [new Assert\NotBlank()],
        
        ]);

        $errors = $this->validator->validate($data, $constraints);
        if (count($errors) > 0) return new Response($errors);

        $email = $data['email'];
        $password = $data['password'];

        $user = $this->repository->findOneBy(["email" => $email]);
        if ($user) return new Response("User already exists", Response::HTTP_BAD_REQUEST);       

        $hashed_password = password_hash(
            $password,
            PASSWORD_BCRYPT
        );

        $user = new User();
        $user
        ->setEmail($email)
        ->setPassword($hashed_password);

        $this->manager->persist($user);
        $this->manager->flush();

        return new JsonResponse($user, Response::HTTP_OK);
    }

    /**
     * @Route("/login", name="login", methods={"POST"})
     */
    public function login(Request $request) 
    {
        $data = json_decode($request->getContent(), true);


        $constraints = new Assert\Collection([
            'email' => [new Assert\NotBlank()],
            'password' => [new Assert\NotBlank()],
        
        ]);

        $errors = $this->validator->validate($data, $constraints);
        if (count($errors) > 0) return new Response($errors);

        $email = $data['email'];
        $password = $data['password'];

        $user = $this->repository->findOneBy(["email" => $email]);
        if (!$user) return new Response("Invalid email", Response::HTTP_NOT_FOUND);
        
        $is_password_correct = password_verify($password, $user->getPassword());
        if (!$is_password_correct) return new Response("Invalid password", Response::HTTP_BAD_REQUEST);
        return new JsonResponse($user, Response::HTTP_OK);
    }
     /**
     * @Route("/user/{id}", name="delete-user", methods={"DELETE"})
     */
    public function deleteUser($id): JsonResponse
    {
        $user = $this->repository->findOneBy(['id' => $id]);
        $this->manager->remove($user);
        $this->manager->flush();
        return new JsonResponse(['status' => 'User deleted!'], Response::HTTP_OK);
    }
}