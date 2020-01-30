<?php

namespace App\Controller;

use App\Criteria\VideoSourceCriteria;
use App\Entity\VideoSource;
use App\Form\EditVideoSourceForm;
use App\Service\VideoSourceService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as FOSRest;

class VideoSourceController extends AbstractFOSRestController implements ClassResourceInterface
{
    /**
     * @var VideoSourceService
     */
    private $videoSourceService;

    /**
     * @param VideoSourceService $videoSourceService
     */
    public function __construct(VideoSourceService $videoSourceService)
    {
        $this->videoSourceService = $videoSourceService;
    }

    /**
     * @FOSRest\Get("/video-source", name="get_video_sources", options={ "method_prefix" = false })
     * @param Request $request
     * @return JsonResponse
     */
    public function getVideoSourcesAction(Request $request)
    {
        $criteria = new VideoSourceCriteria();

        $isRoleAdmin = $this->isGranted('ROLE_ADMIN');
        if (!$isRoleAdmin) {
            $criteria->setStatus('enabled');
        }

        if ($isRoleAdmin) {
            if ($status = $request->query->get('status')) {
                $criteria->setStatus($status);
            }

            if ($embedAllowed = $request->query->get('embed_allowed')) {
                $criteria->setEmbedAllowed($embedAllowed);
            }
        }

        $videoSources = $this->videoSourceService->getAllVideoSourcesByCriteria($criteria);

        $formatted = [];
        foreach ($videoSources as $videoSource) {
            $formatted[] = $videoSource->jsonSerialize();
        }
        
        return $this->createApiResponse($formatted, 200);
    }

    /**
     * @FOSRest\Get("/video-source/{id}", name="get_video_source", options={ "method_prefix" = false })
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getVideoSourceAction(int $id)
    {
        $videoSource = $this->videoSourceService->getVideoSourceById($id);

        if ($videoSource === null) {
            throw new AccessDeniedException();
        }

        $data = $this->serializeVideoSource($videoSource);
        $response = $this->createApiResponse($data, 200);

        return $response;
    }

    /**
     * @FOSRest\Patch("/video-source/{id}", name="update_video_source", options={ "method_prefix" = false })
     *
     * @param string $id
     * @param Request $request
     * @return JsonResponse
     */
    public function editVideoSourceAction(string $id, Request $request)
    {
        $isRoleAdmin = $this->isGranted('ROLE_ADMIN');
        if (!$isRoleAdmin) {
            throw new AccessDeniedException();
        }

        $videoSource = $this->videoSourceService->getVideoSourceById($id);

        if ($videoSource === null) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(EditVideoSourceForm::class, $videoSource);

        $data = json_decode($request->getContent(), true)['resource'];
        $form->submit($data, false);

        if ($form->isSubmitted() && $form->isValid()) {
            $videoSource = $this->mapArrayToObject($data, $videoSource);
            $this->videoSourceService->save($videoSource);
        }

        $data = $this->serializeVideoSource($videoSource);
        $response = $this->createApiResponse($data, 200);

        return $response;
    }

    /**
     * @param array $data
     * @param VideoSource $videoSource
     */
    public function mapArrayToObject(array $data, VideoSource $videoSource)
    {
        if (isset($data['name'])) {
            $videoSource->setName($data['name']);
        }
        if (isset($data['embedAllowed'])) {
            $videoSource->setEmbedAllowed($data['embedAllowed']);
        }
        if (isset($data['embedCode'])) {
            $videoSource->setEmbedCode($data['embedCode']);
        }
        if (isset($data['enabled'])) {
            $videoSource->setStatus($data['status']);
        }
        return $videoSource;
    }

    /**
     * @param VideoSource $videoSource
     * @return array
     */
    public function serializeVideoSource(VideoSource $videoSource)
    {
        return [
            'id' => $videoSource->getId(),
            'name' => $videoSource->getName(),
            'embedAllowed' => $videoSource->getEmbedAllowed(),
            'embedCode' => $videoSource->getEmbedCode(),
            'status' => $videoSource->getStatus()
        ];
    }
}