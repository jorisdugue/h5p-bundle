<?php

namespace Studit\H5PBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/h5p/ajax")
 */
class H5PAJAXController extends AbstractController
{
    protected $h5peditor;
    public function __construct($h5peditor)
    {
        $this->h5peditor = $h5peditor;
    }

    /**
     * Callback that lists all h5p libraries.
     *
     * @Route("/libraries/")
     * @param Request $request
     * @return string
     */
    public function librariesCallback(Request $request)
    {
        if ($request->get('machineName')) {
            return $this->libraryCallback($request);
        }
        //get editor
        $editor = $this->h5peditor;
        $editor->ajax->action(\H5PEditorEndpoints::LIBRARIES);
        exit();
    }

    /**
     * Callback that returns the content type cache
     *
     * @Route("/content-type-cache/")
     */
    public function contentTypeCacheCallback()
    {
        $editor = $this->h5peditor;
        $editor->ajax->action(\H5PEditorEndpoints::CONTENT_TYPE_CACHE);
        exit();
    }

    /**
     * Callback Install library from external file
     *
     * @param string $token Security token
     * @param int $content_id Id of content
     * @param string $machine_name Machine name of library
     * @param Request $request
     *
     * @Route("/library-install/")
     */
    public function libraryInstallCallback(Request $request)
    {
        $editor = $this->h5peditor;
        $editor->ajax->action(
            \H5PEditorEndpoints::LIBRARY_INSTALL,
            $request->get('token', 1),
            $request->get('id')
        );
        exit();
    }

    /**
     * Callback that returns data for a given library
     *
     * @param string $machine_name Machine name of library
     * @param int $major_version Major version of library
     * @param int $minor_version Minor version of library
     * @param Request $request
     */
    private function libraryCallback(Request $request)
    {
        $editor = $this->h5peditor;
        $editor->ajax->action(
            \H5PEditorEndpoints::SINGLE_LIBRARY, $request->get('machineName'),
            $request->get('majorVersion'), $request->get('minorVersion'),
            $request->getLocale(), $this->get('studit_h5p.options')->getOption('storage_dir')
        );
        exit();
    }

    /**
     * Callback for file uploads.
     *
     * @param string $token Security token
     * @param int $content_id Content id
     * @param Request $request
     * @Route("/files/")
     */
    function filesCallback(Request $request)
    {
        $editor = $this->h5peditor;
        $editor->ajax->action(
            \H5PEditorEndpoints::FILES,
            $request->get('token', 1),
            $request->get('id')
        );
        exit();
    }
}