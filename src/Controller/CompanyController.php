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
use App\Entity\Company;

class CompanyController extends AbstractController
{
    private $manager;
    private $repository;
    private $validator;
    
    public function __construct(EntityManagerInterface $manager, ValidatorInterface $validator) {
        $this->manager    = $manager;
        $this->repository = $manager->getRepository(Company::class);
        $this->validator  = $validator;
    }

    /**
     * @Route("/companies", name="get-companies")
     */
   public function getCompanies(Request $request): JsonResponse {
      $companies = $this->repository->findAll();
      return new JsonResponse($companies, Response::HTTP_OK);
   }

    /**
     * @Route("/company", name="create-company", methods={"POST"})
     */
    public function createCompany(Request $request) {
        $data = json_decode($request->getContent(), true);

        $constraints = new Assert\Collection([
            'name' => [new Assert\NotBlank()],
            'address' => [new Assert\NotBlank()],
        
        ]);

        $errors = $this->validator->validate($data, $constraints);
        if (count($errors) > 0) return new Response($errors);

        $name    = $data['name'];
        $address = $data['address'];

        $company = new Company();
        $company->setName($name)->setAddress($address);

        $this->manager->persist($company);
        $this->manager->flush();

        return new JsonResponse(['status' => 'Company added!'], Response::HTTP_OK);
    }

     /**
     * @Route("/company/{id}", name="update-company", methods={"PUT"})
     */
    public function updateCompany(Request $request, $id) {
        $data = json_decode($request->getContent(), true);
        $constraints = new Assert\Collection([
            'name' => [new Assert\NotBlank()],
            'address' => [new Assert\NotBlank()],
        
        ]);

        $errors = $this->validator->validate($data, $constraints);
        if (count($errors) > 0) return new Response($errors);

        $name    = $data['name'];
        $address = $data['address'];

        $company = $this->repository->findOneBy(['id' => $id]);
        $company->setName($name)->setAddress($address);

        $this->manager->persist($company);
        $this->manager->flush();

       
        return new JsonResponse(['status' => 'Company updated!'], Response::HTTP_OK);
    }

      /**
     * @Route("/company/{id}", name="delete-company", methods={"DELETE"})
     */
    public function deleteCompany($id): JsonResponse
    {
        $company = $this->repository->findOneBy(['id' => $id]);
        $this->manager->remove($company);
        $this->manager->flush();
        return new JsonResponse(['status' => 'Company deleted!'], Response::HTTP_OK);
    }
}
