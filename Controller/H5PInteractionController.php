<?php

namespace Studit\H5PBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use H5PCore;
use Studit\H5PBundle\Core\H5PIntegration;
use Studit\H5PBundle\Core\H5POptions;
use Studit\H5PBundle\Entity\Content;
use Studit\H5PBundle\Entity\ContentUserData;
use Studit\H5PBundle\Service\ResultService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/h5p/interaction')]
class H5PInteractionController extends AbstractController
{
    protected EntityManagerInterface $entityManager;
    protected ResultService $resultService;
    protected SerializerInterface $serializer;
    protected Packages $assetsPaths;
    protected H5POptions $options;
    protected H5PIntegration $h5PIntegration;
    protected H5PCore $h5PCore;
    protected KernelInterface $kernel;

    public function __construct(
        EntityManagerInterface $entityManager,
        ResultService $resultService,
        SerializerInterface $serializer,
        Packages $packages,
        H5POptions $options,
        H5PIntegration $h5PIntegration,
        H5PCore $h5PCore,
        KernelInterface $kernel
    ) {
        $this->entityManager = $entityManager;
        $this->resultService = $resultService;
        $this->serializer = $serializer;
        $this->assetsPaths = $packages;
        $this->options = $options;
        $this->h5PIntegration = $h5PIntegration;
        $this->h5PCore = $h5PCore;
        $this->kernel = $kernel;
    }

    #[Route("/set-finished/{token}")]

    /**
     * Access callback for the setFinished feature.
     *
     * @param Request $request Current request
     * @param $token
     * @return JsonResponse
     */
    public function setFinished(Request $request, $token): JsonResponse
    {
        if (!\H5PCore::validToken('result', $token)) {
            \H5PCore::ajaxError('Invalid security token');
        }
        $result = $this->resultService->handleRequestFinished(
            $request,
            $this->h5PIntegration->getUserId($this->getUser())
        );
        $this->entityManager->persist($result);
        $this->entityManager->flush();
        return new JsonResponse(['success' => true]);
    }

    #[Route("/content-user-data/{contentId}/{dataType}/{subContentId}")]

    /**
     * Handles insert, updating and deleting content user data through AJAX.
     *
     * @param Request $request Current request
     * @param $contentId
     * @param $dataType
     * @param $subContentId
     * @return JsonResponse
     * @throws Exception
     */
    public function contentUserData(Request $request, $contentId, $dataType, $subContentId): JsonResponse
    {
        if (!$contentId) {
            return new JsonResponse(['success' => false, 'message' => 'No content']);
        }

        $user = $this->getUser();
        $data = $request->get("data");
        $preload = $request->get("preload");
        $invalidate = $request->get("invalidate");
        $em = $this->entityManager;
        if ($data !== null && $preload !== null && $invalidate !== null) {
            if (!\H5PCore::validToken('contentuserdata', $request->get("token"))) {
                return new JsonResponse(['success' => false, 'message' => 'No content']);
            }

            //remove data if data = 0
            if ($data === '0') {
                //remove data here
                $this->resultService->removeData($contentId, $dataType, $user, $subContentId);
            } else {
                // Wash values to ensure 0 or 1.
                $preload = ($preload === '0' ? 0 : 1);
                $invalidate = ($invalidate === '0' ? 0 : 1);

                //get if exists
                /**
                 * @var ContentUserData $update
                 */
                $update = $em->getRepository("Studit\H5PBundle\Entity\ContentUserData")->findOneBy(
                    [
                        'subContentId' => $subContentId,
                        'mainContent' => $contentId,
                        'dataId' => $dataType,
                        'user' => $this->h5PIntegration->getUserId($user),
                    ]
                );
                if (!$update) {
                    /**
                     * insert data
                     * @var ContentUserData $contentUserData
                     */
                    $contentUserData = new ContentUserData();
                    $contentUserData->setUser($this->h5PIntegration->getUserId($user));
                    $contentUserData->setData($data);
                    $contentUserData->setDataId($dataType);
                    $contentUserData->setSubContentId($subContentId);
                    $contentUserData->setPreloaded($preload);
                    $contentUserData->setDeleteOnContentChange($invalidate);
                    $contentUserData->setTimestamp(time());

                    /** @var Content|null $content */
                    $content = $em->getRepository('Studit\H5PBundle\Entity\Content')->findOneBy(['id' => $contentId]);
                    $contentUserData->setMainContent($content);
                    $em->persist($contentUserData);
                } else {
                    //update data
                    $update->setTimestamp(time());
                    $update->setPreloaded($preload);
                    $update->setData($data);
                    $update->setDeleteOnContentChange($invalidate);
                    $em->persist($update);
                }
                $em->flush();
            }

            return new JsonResponse(['success' => true]);
        } else {
            $data = $em->getRepository("Studit\H5PBundle\Entity\ContentUserData")->findOneBy([
                'subContentId' => $subContentId,
                'mainContent' => $contentId,
                'dataId' => $dataType,
                'user' => $this->h5PIntegration->getUserId($user),
            ]);

            //decode for read the information
            return new JsonResponse([
                'success' => true,
                'data' => json_decode($this->serializer->serialize($data, 'json')),
            ]);
        }
    }

    #[Route("/embed/{content}")]

    /**
     * @param Request $request Current request
     * @param Content $content
     * @return Response
     */
    public function embedAction(Request $request, Content $content): Response
    {
        $id = $content->getId();
        $response = [
            '#cache' => [
                'tags' => [
                    'h5p_content:' . $content->getId()
                ],
            ],
        ];
        $h5p_content = $content;
        // Grab the core integration settings
        $integration = $this->h5PIntegration->getGenericH5PIntegrationSettings();
        $content_id_string = 'cid-' . $content->getId();
        // Add content specific settings
        $integration['contents'][$content_id_string] = $this->h5PIntegration->getH5PContentIntegrationSettings(
            $content
        );
        $preloaded_dependencies = $this->h5PCore->loadContentDependencies($content->getId(), 'preloaded');
        $files = $this->h5PCore->getDependenciesFiles($preloaded_dependencies, $this->options->getRelativeH5PPath());
        // Load public files
        $jsFilePaths = array_map(function ($asset) {
            return $asset->path;
        }, $files['scripts']);
        $cssFilePaths = array_map(function ($asset) {
            return $asset->path;
        }, $files['styles']);
        // Load core assets
        $coreAssets = $this->h5PIntegration->getCoreAssets();
        // Merge assets
        $scripts = array_merge($coreAssets['scripts'], $jsFilePaths);
        $styles = array_merge($cssFilePaths, $coreAssets['styles']);
        // Render the page and add to the response
        ob_start();
        //Add locale
        $lang = $request->getLocale();
        $content = [
            'id' => $id,
            'title' => "H5P Content $id",
        ];
        //include the embed file (provide in h5p-core)
        include $this->kernel->getProjectDir() . '/vendor/h5p/h5p-core/embed.php';
        $response['#markup'] = ob_get_clean();
        //return nes Response HTML
        return new Response($response['#markup']);
    }

    /**
     * Retrieves the URL for the H5P asset based on the configured asset path.
     *
     * This method generates and returns the URL for the H5P asset by using the
     * configured asset path provided through the options service. The URL is
     * constructed using the asset path and the Symfony Asset component.
     *
     * @return string The URL for the H5P asset.
     */
    private function getH5PAssetUrl(): string
    {
        return $this->assetsPaths->getUrl($this->options->getH5PAssetPath());
    }
}
