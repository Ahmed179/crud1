<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use App\Entity\Employee;
use App\Entity\Company;
use App\Entity\Role;

class EmployeeController extends AbstractController
{
    private $manager;
    private $repository;

    public function __construct(EntityManagerInterface $manager, ValidatorInterface $validator, MailerInterface $mailer) {
        $this->manager    = $manager;
        $this->repository = $manager->getRepository(Employee::class);
        $this->validator  = $validator;
        $this->mailer     = $mailer;
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
    public function createEmployee(Request $request, $appEmail) {
        $data = json_decode($request->getContent(), true);

        $constraints = new Assert\Collection([
            'firstname' => [new Assert\NotBlank()],
            'lastname' => [new Assert\NotBlank()],
            'phone' => [new Assert\NotBlank()],
            'password' => [new Assert\NotBlank()],
            'email' => [new Assert\NotBlank(), new Assert\Email()],
            'company_id' => [new Assert\NotBlank()],
            'role_ids' => [new Assert\NotBlank()],
        ]);

        $errors = $this->validator->validate($data, $constraints);
        if (count($errors) > 0) return new Response($errors);

        $firstname  = $data['firstname'];
        $lastname   = $data['lastname'];
        $phone      = $data['phone'];
        $email      = $data['email'];
        $password   = $data['password'];
        $company_id = $data['company_id'];
        $role_ids   = $data['role_ids'];

        $company_repository = $this->manager->getRepository(Company::class);
        $company = $company_repository->findOneBy(['id' => $company_id]);

        $role_repository = $this->manager->getRepository(Role::class);
        $roles = $role_repository->findAll();
     
        
        $employee = new Employee();
        $employee
            ->setFirstName($firstname)
            ->setLastName($lastname)
            ->setPhone($phone)
            ->setEmail($email)
            ->setPassword($password)
            ->setCompany($company);

        foreach ($roles as $role) {
            if (in_array($role->getId(), $role_ids)){
                $employee->addRole($role);
            }
        }
        
        $this->manager->persist($employee);
        $this->manager->flush();

        $email = (new Email())
            ->from($appEmail)
            ->to(new Address($email, $firstname))
            ->subject('you have been hired!')
            ->html('<p>Thank you for applying, you have been hired! </p>');

        $this->mailer->send($email);
        return new JsonResponse(['status' => 'Employee added!'], Response::HTTP_OK);
    }
    

     /**
     * @Route("/employee/{id}", name="update-employee", methods={"PUT"})
     */
    public function updateEmployee(Request $request, $id) {
        $data = json_decode($request->getContent(), true);

        $constraints = new Assert\Collection([
            'firstname' => [new Assert\NotBlank()],
            'lastname' => [new Assert\NotBlank()],
            'phone' => [new Assert\NotBlank()],
            'password' => [new Assert\NotBlank()],
            'email' => [new Assert\NotBlank(), new Assert\Email()],
            'company_id' => [new Assert\NotBlank()],
            'role_id' => [new Assert\NotBlank()],
        ]);

        $errors = $this->validator->validate($data, $constraints);
        if (count($errors) > 0) return new Response($errors);


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

            $errors = $this->validator->validate($employee);

            if (count($errors) > 0) {
                $errorsString = (string) $errors;
                return new Response($errorsString);
            }

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
