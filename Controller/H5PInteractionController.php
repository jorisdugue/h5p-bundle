<?php


namespace Studit\H5PBundle\Controller;


use Studit\H5PBundle\Entity\Content;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/h5p/interaction")
 */
class H5PInteractionController extends AbstractController{
    /**
     * Access callback for the setFinished feature
     *
     * @Route("/set-finished/{token}")
     * @param Request $request
     * @param $token
     * @return JsonResponse
     */
    public function setFinished(Request $request, $token)
    {
        if (!\H5PCore::validToken('result', $token)) {
            \H5PCore::ajaxError('Invalid security token');
        }
        return new JsonResponse();
        /*@var ResultService $rs */
        //$rs = $this->get('studit_h5p.result_storage');
        //$result = $rs->handleRequest($request, $this->getUser()->getId());
        /* $em = $this->getDoctrine()->getManager();
         $em->persist($result);
         $em->flush();
         return new JsonResponse(['success' => true]);*/
    }
    /**
     * Handles insert, updating and deleting content user data through AJAX.
     *
     * @Route("/content-user-data/{contentId}/{dataType}/{subContentId}")
     * @param Request $request
     * @param $contentId
     * @param $dataType
     * @param $subContentId
     * @return JsonResponse
     */
    public function contentUserData(Request $request, $contentId, $dataType, $subContentId)
    {
        return new JsonResponse();
    }
    /**
     * @Route("/embed/{content}")
     * @param Request $request
     * @param Content $content
     * @return Response
     */
    public function embedAction(Request $request, Content $content)
    {
        $id= $content->getId();
        $response = [
            '#cache' => [
                'tags' => [
                    'h5p_content:' . $content->getId()
                ],
            ],
        ];
        $h5p_content = $content;
        if (empty($h5p_content)){
            //change url here
            $response['#markup'] = '<body style="margin:0"><div style="background: #fafafa url(' . $this->getH5PAssetUrl() . '/h5p-core/images/h5p.svg) no-repeat center;background-size: 50% 50%;width: 100%;height: 100%;"></div><div style="width:100%;position:absolute;top:75%;text-align:center;color:#434343;font-family: Consolas,monaco,monospace">' . t('Content unavailable.') . '</div></body>';
            return new Response($response['#markup']);
        }
        // Grab the core integration settings
        $integration = $this->get('studit_h5p.integration')->getGenericH5PIntegrationSettings();
        $content_id_string = 'cid-' . $content->getId();
        // Add content specific settings
        $integration['contents'][$content_id_string] = $this->get('studit_h5p.integration')->getH5PContentIntegrationSettings($content);
        $preloaded_dependencies = $this->get('studit_h5p.core')->loadContentDependencies($content->getId(), 'preloaded');
        $files = $this->get('studit_h5p.core')->getDependenciesFiles($preloaded_dependencies, $this->get('studit_h5p.options')->getRelativeH5PPath());
        // Load public files
        $jsFilePaths = array_map(function ($asset) {
            return $asset->path;
        }, $files['scripts']);
        $cssFilePaths = array_map(function ($asset) {
            return $asset->path;
        }, $files['styles']);
        // Load core assets
        $coreAssets = $this->get('studit_h5p.integration')->getCoreAssets();
        // Merge assets
        $scripts = array_merge($coreAssets['scripts'], $jsFilePaths);
        $styles = array_merge($cssFilePaths, $coreAssets['styles']);
        // Render the page and add to the response
        ob_start();
        //Add locale
        $lang = $request->getLocale();
        $content = [
            'id' => $id,
            'title' => "H5P Content {$id}",
        ];
        //include the embed file (provide in h5p-core)
        include $this->container->getParameter('kernel.root_dir').'/../vendor/h5p/h5p-core/embed.php';
        $response['#markup'] = ob_get_clean();
        //return nes Response HTML
        return new Response($response['#markup']);
    }
}