<?php

namespace App\Controller;

use App\Entity\Voiture;
use App\Form\SearchType;
use App\Form\VoitureType;
use Doctrine\ORM\QueryBuilder;
use App\Repository\VoitureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Component\HttpFoundation\Request;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\Column\TwigColumn;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Omines\DataTablesBundle\Adapter\ArrayAdapter;
use Omines\DataTablesBundle\Column\DateTimeColumn;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/voiture')]
class VoitureController extends AbstractController
{
    #[Route('/{marque}', name: 'app_voiture_index')]
    public function index(VoitureRepository $voitureRepository,DataTableFactory $dataTableFactory,Request $request,$marque = null): Response
    {
        $form = $this->createForm(SearchType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
          
            $data = $form->getData();
            $marque = $data['marque']->getMarque(); 
            return $this->redirectToRoute('app_voiture_index',['marque'=>$marque]);      
        }
            $table =$dataTableFactory->create()
            ->add('marque', TextColumn::class,['label' => 'Marque'])
            ->add('nom', TextColumn::class,['label' => 'Nom'])
            ->add('couleur', TextColumn::class,['label' => 'Couleur'])
            ->add('version', DateTimeColumn::class,
            ['label' => 'Version', 'format' => 'Y-m-d ', 'orderable' => true])
            ->add('kilometrage', TextColumn::class,['label' => 'kilometrage'])
            ->add('moteur', TextColumn::class,['label' => 'Moteur'])
            ->add('buttons', TwigColumn::class, [
                'className' => 'buttons',
                'label'=>'action',
                'template' => 'voiture/tables/update.html.twig',
            ])
          
            ->createAdapter(ORMAdapter::class, [
                'entity' => Voiture::class,

                'query' => function (QueryBuilder $builder)use($marque) {
                    $builder
                        ->select('v')
                        ->from(Voiture::class, 'v');
                       if ($marque){
                        $builder
                        ->where('v.marque = :marque')
                        ->setParameter('marque', $marque);
                       }
                        
                       
                       
                    ;
                },
            ])
            ->handleRequest($request);
              
        
        

        if ($table->isCallback()) {
            return $table->getResponse();
        }
        return $this->render('voiture/index.html.twig', [
            'voitures' => $voitureRepository->findAll(),
            'datatable' => $table,
            'form'=>$form
        ]);
    }

    #[Route('/new', name: 'app_voiture_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $voiture = new Voiture();
        $form = $this->createForm(VoitureType::class, $voiture);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($voiture);
            $entityManager->flush();

            return $this->redirectToRoute('app_voiture_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('voiture/new.html.twig', [
            'voiture' => $voiture,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_voiture_show', methods: ['GET'])]
    public function show(Voiture $voiture): Response
    {
        return $this->render('voiture/show.html.twig', [
            'voiture' => $voiture,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_voiture_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Voiture $voiture, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(VoitureType::class, $voiture);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_voiture_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('voiture/edit.html.twig', [
            'voiture' => $voiture,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_voiture_delete', methods: ['POST'])]
    public function delete(Request $request, Voiture $voiture, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$voiture->getId(), $request->request->get('_token'))) {
            $entityManager->remove($voiture);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_voiture_index', [], Response::HTTP_SEE_OTHER);
    }
}
