<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Employee;
use App\Entity\Company;
use App\Entity\Role;

class EmployeeController extends AbstractController
{
    private $manager;
    private $repository;

    public function __construct(EntityManagerInterface $manager) {
        $this->manager    = $manager;
        $this->repository = $manager->getRepository(Employee::class);
    }

    /**
     * @Route("/employees", name="get-employees")
     */
   public function getEmployees(Request $request): JsonResponse {
      $companies = $this->repository->findAll();
      return new JsonResponse($companies, Response::HTTP_OK);
   }

    /**
     * @Route("/employee", name="create-employee", methods={"POST"})
     */
    public function createEmployee(Request $request): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $firstname  = $data['firstname'];
        $lastname   = $data['lastname'];
        $phone      = $data['phone'];
        $email      = $data['email'];
        $password   = $data['password'];
        $company_id = $data['company_id'];
        $role_id    = $data['role_id'];

        $company_repository = $this->manager->getRepository(Company::class);
        $company = $company_repository->findOneBy(['id' => $company_id]);

        $role_repository = $this->manager->getRepository(Role::class);
        $role = $role_repository->findOneBy(['id' => $role_id]);
        
        $employee = new Employee();
        $employee
            ->setFirstName($firstname)
            ->setLastName($lastname)
            ->setPhone($phone)
            ->setEmail($email)
            ->setPassword($password)
            ->setCompany($company)
            ->setRole($role);

        $this->manager->persist($employee);
        $this->manager->flush();

        return new JsonResponse(['status' => 'Employee added!'], Response::HTTP_OK);
    }

     /**
     * @Route("/employee/{id}", name="update-employee", methods={"PUT"})
     */
    public function updateEmployee(Request $request, $id): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $firstname  = $data['firstname'];
        $lastname   = $data['lastname'];
        $phone      = $data['phone'];
        $email      = $data['email'];
        $password   = $data['password'];
        $company_id = $data['company_id'];
        $role_id    = $data['role_id'];

        $company_repository = $this->manager->getRepository(Company::class);
        $company = $company_repository->findOneBy(['id' => $company_id]);

        $role_repository = $this->manager->getRepository(Role::class);
        $role = $role_repository->findOneBy(['id' => $role_id]);

        $employee = $this->repository->findOneBy(['id' => $id]);
        $employee
            ->setFirstName($firstname)
            ->setLastName($lastname)
            ->setPhone($phone)
            ->setEmail($email)
            ->setPassword($password)
            ->setCompany($company)
            ->setRole($role);

        $this->manager->persist($employee);
        $this->manager->flush();

        return new JsonResponse(['status' => 'Employee updated!'], Response::HTTP_OK);
    }

      /**
     * @Route("/employee/{id}", name="delete-employee", methods={"DELETE"})
     */
    public function deleteEmployee($id): JsonResponse
    {
        $employee = $this->repository->findOneBy(['id' => $id]);
        $this->manager->remove($employee);
        $this->manager->flush();
        return new JsonResponse(['status' => 'Employee deleted!'], Response::HTTP_OK);
    }
}
