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
use App\Entity\Role;

class RoleController extends AbstractController
{
    private $manager;
    private $repository;

    public function __construct(EntityManagerInterface $manager, ValidatorInterface $validator) {
        $this->manager    = $manager;
        $this->repository = $manager->getRepository(Role::class);
        $this->validator  = $validator;
    }

    /**
     * @Route("/roles", name="get-roles")
     */
   public function getRoles(Request $request): JsonResponse {
      $roles = $this->repository->findAll();
      return new JsonResponse($roles, Response::HTTP_OK);
   }

    /**
     * @Route("/role", name="create-role", methods={"POST"})
     */
    public function createRole(Request $request) {
        $data = json_decode($request->getContent(), true);

        $constraints = new Assert\Collection([
            'name' => [new Assert\NotBlank()],
        ]);

        $errors = $this->validator->validate($data, $constraints);
        if (count($errors) > 0) return new Response($errors);

        $name = $data['name'];
        
        $role = new Role();
        $role->setName($name);

        $this->manager->persist($role);
        $this->manager->flush();

        return new JsonResponse(['status' => 'role added!'], Response::HTTP_OK);
    }

     /**
     * @Route("/role/{id}", name="update-role", methods={"PUT"})
     */
    public function updateRole(Request $request, $id) {
        $data = json_decode($request->getContent(), true);

        $constraints = new Assert\Collection([
            'name' => [new Assert\NotBlank()],
        ]);

        $errors = $this->validator->validate($data, $constraints);
        if (count($errors) > 0) return new Response($errors);

        $name = $data['name'];

        $role = $this->repository->findOneBy(['id' => $id]);
        $role->setName($name);

        $this->manager->persist($role);
        $this->manager->flush();

        return new JsonResponse(['status' => 'role updated!'], Response::HTTP_OK);
    }

      /**
     * @Route("/role/{id}", name="delete-role", methods={"DELETE"})
     */
    public function deleteRole($id): JsonResponse
    {
        $role = $this->repository->findOneBy(['id' => $id]);
        $this->manager->remove($role);
        $this->manager->flush();
        return new JsonResponse(['status' => 'role deleted!'], Response::HTTP_OK);
    }
}
