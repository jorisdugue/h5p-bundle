<?php


namespace Studit\H5PBundle\Controller;

use Studit\H5PBundle\Entity\Content;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Studit\H5PBundle\Core\H5PIntegration;
use Studit\H5PBundle\Form\Type\H5PType;

/**
 * @Route("/h5p/")
 */
class H5PController extends AbstractController
{
    protected $serviceh5pcore;
    protected $serviceh5pintegeration;
    public function __construct($core, $integration)
    {
        $this->serviceh5pcore = $core;
        $this->serviceh5pintegeration = $integration;
    }

    /**
     * List of all H5P content
     * @Route("list")
     */
    public function listAction()
    {
        $contents = $this->getDoctrine()->getRepository('StuditH5PBundle:Content')->findAll();
        return $this->render('@StuditH5P/list.html.twig', ['contents' => $contents]);
    }
    /**
     * Show content of H5P created by user
     * @Route("show/{content}")
     * @param Content $content
     * @return Response
     */
    public function showAction(Content $content)
    {
        $h5pIntegration = $this->serviceh5pintegeration->getGenericH5PIntegrationSettings();
        $contentIdStr = 'cid-' . $content->getId();
        $h5pIntegration['contents'][$contentIdStr] = $this->serviceh5pintegeration->getH5PContentIntegrationSettings($content);
        $preloaded_dependencies = $this->serviceh5pcore->loadContentDependencies($content->getId(), 'preloaded');
        $files = $this->serviceh5pcore->getDependenciesFiles($preloaded_dependencies, $this->get('studit_h5p.options')->getRelativeH5PPath());
        if ($content->getLibrary()->isFrame()) {
            $jsFilePaths = array_map(function ($asset) {
                return $asset->path;
            }, $files['scripts']);
            $cssFilePaths = array_map(function ($asset) {
                return $asset->path;
            }, $files['styles']);
            $coreAssets = $this->serviceh5pintegeration->getCoreAssets();
            $h5pIntegration['core']['scripts'] = $coreAssets['scripts'];
            $h5pIntegration['core']['styles'] = $coreAssets['styles'];
            $h5pIntegration['contents'][$contentIdStr]['scripts'] = $jsFilePaths;
            $h5pIntegration['contents'][$contentIdStr]['styles'] = $cssFilePaths;
        }
        return $this->render('@StuditH5P/show.html.twig', ['contentId' => $content->getId(), 'isFrame' => $content->getLibrary()->isFrame(), 'h5pIntegration' => $h5pIntegration, 'files' => $files]);
    }
    /**
     * @Route("new")
     * @param Request $request
     * @param H5PIntegration $h5PIntegration
     * @return
     */
    public function newAction(Request $request, H5PIntegration $h5PIntegration)
    {
        return $this->handleRequest($request,$h5PIntegration );
    }
    /**
     * @Route("edit/{content}")
     * @param Request $request
     * @param Content $content
     * @return
     */
    public function editAction(Request $request, Content $content)
    {
        return $this->handleRequest($request, $content);
    }
    private function handleRequest(Request $request,H5PIntegration $h5PIntegration, Content $content = null)
    {
        $formData = null;
        if ($content) {
            $formData['parameters'] = $content->getParameters();
            $formData['library'] = (string)$content->getLibrary();
        }
        $form = $this->createForm(H5pType::class, $formData);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $contentId = $this->get('studit_h5p.library_storage')->storeLibraryData($data['library'], $data['parameters'], $content);
            return $this->redirectToRoute('studit_h5p_h5p_show', ['content' => $contentId]);
        }
        $h5pIntegration = $h5PIntegration->getEditorIntegrationSettings($content ? $content->getId() : null);
        return $this->render('@StuditH5P/edit.html.twig', ['form' => $form->createView(), 'h5pIntegration' => $h5pIntegration, 'h5pCoreTranslations' => $this->serviceh5pintegeration->getTranslationFilePath()]);
    }
    /**
     * @Route("delete/{contentId}")
     * @param integer $contentId
     * @return RedirectResponse
     */
    public function deleteAction($contentId)
    {
        $this->get('studit_h5p.storage')->deletePackage([
            'id' => $contentId,
            'slug' => 'interactive-content'
        ]);
        return $this->redirectToRoute('studit_h5p_h5p_list');
    }
}
