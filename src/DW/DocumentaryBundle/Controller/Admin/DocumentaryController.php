<?php

namespace DW\DocumentaryBundle\Controller\Admin;

use DW\BaseBundle\Enum\Order;
use DW\DocumentaryBundle\Criteria\DocumentaryCriteria;
use DW\DocumentaryBundle\Entity\Documentary;
use DW\DocumentaryBundle\Enum\OrderBy;
use DW\DocumentaryBundle\Form\DocumentaryType;
use DW\DocumentaryBundle\Service\DocumentaryService;
use DW\DocumentaryBundle\Uploader\PosterUploader;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DocumentaryController extends Controller
{
    /**
     * @param Request $request
     * @return Response
     */
    public function listAction(Request $request)
    {
        $page = $request->query->get('page', 1);

        $criteria = new DocumentaryCriteria();
        $criteria->setSort([
            OrderBy::CREATED_AT => Order::DESC
        ]);

        $documentaryService = $this->getDocumentaryService();
        $qb = $documentaryService->getDocumentariesByCriteriaQueryBuilder($criteria);

        $adapter = new DoctrineORMAdapter($qb, false);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(5);
        $pagerfanta->setCurrentPage($page);

        return $this->render('DocumentaryBundle:Admin:list.html.twig', [
            'documentaries' => $pagerfanta
        ]);
    }

    /**
     * @param string $slug
     * @return Response
     */
    public function showAction(string $slug)
    {
        $documentaryService = $this->getDocumentaryService();
        $documentary = $documentaryService->getDocumentaryBySlug($slug);

        if ($documentary == null) {
            $this->createNotFoundException();
        }

        return $this->render('DocumentaryBundle:Admin:show.html.twig', [
            'documentary' => $documentary
        ]);
    }

    /**
     * @param Request $request
     * @param string $slug
     * @return Response
     */
    public function editAction(Request $request, string $slug)
    {
        $documentaryService = $this->getDocumentaryService();
        $documentary = $documentaryService->getDocumentaryBySlug($slug);

        if ($documentary == null) {
            $this->createNotFoundException();
        }

        $form = $this->createForm(DocumentaryType::class, $documentary);
        $form->get('poster')->setData(
            new File($this->getParameter('posters_directory').'/'.$documentary->getPoster())
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('poster')->getData();

            $posterUploader = $this->getPosterUploader();
            $fileName = $posterUploader->upload($file);

            $documentary->setPoster($fileName);

            $documentaryService->save($documentary);
        }

        return $this->render('DocumentaryBundle:Admin:edit.html.twig',[
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function createAction(Request $request)
    {
        $documentary = new Documentary();

        $form = $this->createForm(DocumentaryType::class, $documentary);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('poster')->getData();

            $posterUploader = $this->getPosterUploader();
            $fileName = $posterUploader->upload($file);

            $documentary->setPoster($fileName);

            $documentaryService = $this->getDocumentaryService();
            $documentaryService->save($documentary);
        }

        return $this->render('DocumentaryBundle:Admin:create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @return DocumentaryService
     */
    private function getDocumentaryService()
    {
        return $this->get('dw.documentary_service');
    }

    /**
     * @return PosterUploader
     */
    private function getPosterUploader()
    {
        return $this->get('dw.poster_uploader');
    }
}