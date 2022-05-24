<?php

namespace App\Application\Controller;

use App\Entity\User;
use App\Application\ConstructorEngine\Templater;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class B2BController extends AbstractController
{
    private ObjectManager $entityManager;
    private User $currentUser;

    public function __construct(
        ManagerRegistry $doctrine
    ) {
//        Templater::create();
//        $this->entityManager = $doctrine->getManager();
//        $this->currentUser = $this->userRepository->find(1);
    }

//    #[Route('/b2b/electronic-auction/purchases', name: 'b2b_ea_purchases')]
//    public function index(): Response
//    {
//        $purchases = $this->purchaseRepository->findEAPurchases();
//
//        return $this->renderForm(
//            'b2b/style1/index.twig',
//            [
//                'purchases' => $purchases,
//            ]
//        );
//    }


//    #[Route('/b2b/procedure-info/{purchaseId}', name: 'b2b_procedure_info')]
//    public function procedureInfo(int $purchaseId): Response
//    {
//        $purchase = $this->purchaseRepository->find($purchaseId);
//
//        if (!$purchase) {
//            return new Response('Нет такой закупки');
//        }
//
//        return $this->renderForm(
//            'b2b/style1/procedure-info.html.twig',
//            [
//                'purchase' => $purchase,
//            ]
//        );
//    }

//    #[Route('/forms', name: 'forms')]
//    public function forms(Request $request): Response
//    {
//        $package = (new Package())->setUser($this->currentUser);
//        $packageForm = $this->createForm(PackageCreationType::class, $package);
//        $packageProcessForm = $this->createForm(ProcessPackageType::class);
//
//        $packageForm->handleRequest($request);
//        if ($packageForm->isSubmitted() && $packageForm->isValid()) {
//            /** @var Package $newPackage */
//            $newPackage = $packageForm->getData();
//            $this->entityManager->persist($newPackage);
//            $this->entityManager->flush();
//
//            return new Response("Package added!");
//        }
//
//        $packageProcessForm->handleRequest($request);
//        if ($packageProcessForm->isSubmitted() && $packageProcessForm->isValid()) {
//            /** @var Package $packageToProcess */
//            $packageToProcess = $packageProcessForm->getData()['electronicAuctionPackages'];
//            $packageToProcess->setState(Package::processed);
//            $this->entityManager->flush();
//        }
//
//        return $this->renderForm(
//            'b2b/forms.html',
//            [
//                'packageForm' => $packageForm,
//                'packageProcessForm' => $packageProcessForm,
//            ],
//        );
//    }

//    #[Route('/another-table/{purchaseId}', name: 'anotherTable')]
//    public function anotherTable(int $purchaseId): Response
//    {
//        $purchase = $this->purchaseRepository->find($purchaseId);
//
//        return $this->renderForm(
//            'b2b/style2/table.html.twig',
//            [
//                'purchase' => $purchase,
//            ],
//        );
//    }

//    #[Route('/preview', name: 'previewAllTemplates')]
//    public function previewAllTemplates(): Response
//    {
//        $templateModules = (Templater::getTemplaterObject())->getModulesTemplatesList();
//
//        return $this->renderForm(
//            '@vendorTemplates/preview.twig',
//            [
//                'templateModules' => $templateModules
//            ],
//        );
//    }

//    #[Route('/tryTemplate', name: 'tryTemplate')]
//    public function tryTemplate(): Response
//    {
//        return $this->renderForm(
//            'blank.twig',
//            [
//            ],
//        );
//    }

//    #[Route('/menu', name: 'menu')]
//    public function menu(): Response
//    {
//        die("sdflkjsidlf");
//    }
}
