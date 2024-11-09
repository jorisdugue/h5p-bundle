<?php

namespace Studit\H5PBundle\Controller;

use Exception;
use Studit\H5PBundle\Core\H5POptions;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/h5p/ajax')]
class H5PAJAXController extends AbstractController
{
    protected \H5peditor $h5peditor;
    protected H5POptions $serviceh5poptions;

    public function __construct(\H5peditor $h5peditor, H5POptions $h5poption)
    {
        $this->h5peditor = $h5peditor;
        $this->serviceh5poptions = $h5poption;
    }

    #[Route('/libraries/')]

    /**
     * Callback that lists all h5p libraries.
     *
     * @param Request $request Current request
     * @return JsonResponse
     */
    public function librariesCallback(Request $request): JsonResponse
    {
        ob_start();

        if ($request->get('machineName')) {
            return $this->libraryCallback($request);
        }

        //get editor
        $editor = $this->h5peditor;
        $editor->ajax->action(\H5PEditorEndpoints::LIBRARIES);

        $output = ob_get_contents();
        ob_end_clean();

        return $this->json(json_decode($output, true));
    }

    #[Route('/content-type-cache/')]

    /**
     * Callback that returns the content type cache.
     * @return JsonResponse
     */
    public function contentTypeCacheCallback(): JsonResponse
    {
        ob_start();

        $editor = $this->h5peditor;
        $editor->ajax->action(\H5PEditorEndpoints::CONTENT_TYPE_CACHE);

        $output = ob_get_contents();
        ob_end_clean();
        return $this->json(json_decode($output, true));
    }

    #[Route('/content-hub-metadata-cache')]

    /**
     * Callback that return the content hub metadata cache.
     * @param Request $request Current Request
     * @return JsonResponse
     */
    public function contentHubMetadataCache(Request $request): JsonResponse
    {
        $locale = $request->getLocale() !== null ? $request->getLocale() : 'en';
        ob_start();

        $editor = $this->h5peditor;
        $editor->ajax->action(\H5PEditorEndpoints::CONTENT_HUB_METADATA_CACHE, $locale);

        $output = ob_get_contents();
        ob_end_clean();

        return $this->json(json_decode($output, true));
    }

    #[Route('/translations/')]

    /**
     * Callback for translations.
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function TranslationsCallback(Request $request): JsonResponse
    {
        ob_start();

        $editor = $this->h5peditor;
        $language = $request->get('language');
        $editor->ajax->action(\H5PEditorEndpoints::TRANSLATIONS, $language);

        $output = ob_get_contents();
        ob_end_clean();

        return $this->json(json_decode($output, true));
    }

    #[Route('/library-install/')]

    /**
     * Callback Install library from external file.
     * @param Request $request Current request
     *
     * @return JsonResponse
     */
    public function libraryInstallCallback(Request $request): JsonResponse
    {
        ob_start();

        $editor = $this->h5peditor;
        $editor->ajax->action(
            \H5PEditorEndpoints::LIBRARY_INSTALL,
            $request->get('token', 1),
            $request->get('id')
        );

        $output = ob_get_contents();
        ob_end_clean();

        return $this->json(json_decode($output, true));
    }

    /**
     * Callback that returns data for a given library.
     *
     * @param Request $request Current Request
     * @return JsonResponse
     */
    private function libraryCallback(Request $request): JsonResponse
    {
        ob_start();

        //$machineName, $majorVersion, $minorVersion, $languageCode, $prefix = '', $fileDir = '', $defaultLanguage
        $editor = $this->h5peditor;
        $locale = $request->getLocale() !== null ? $request->getLocale() : 'en';
        $editor->ajax->action(
            \H5PEditorEndpoints::SINGLE_LIBRARY,
            $request->get('machineName'),
            $request->get('majorVersion'),
            $request->get('minorVersion'),
            $locale,
            $this->serviceh5poptions->getRelativeH5PPath(),
            '',
            $locale
        );

        $output = ob_get_contents();
        ob_end_clean();

        return $this->json(json_decode($output, true));
    }

    #[Route('/library-upload/')]

    /**
     * Callback for uploading a library.
     *
     * @param Request $request Current Request
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function libraryUploadCallback(Request $request): JsonResponse
    {
        ob_start();

        $editor = $this->h5peditor;
        $filePath = null;
        if (isset($_FILES['h5p'])) {
            $filePath = $_FILES['h5p']['tmp_name'];
        } else {
            //generate error
            throw new Exception('POST file is missing');
        }

        $editor->ajax->action(
            \H5PEditorEndpoints::LIBRARY_UPLOAD,
            $request->get('token', 1),
            $filePath,
            $request->get('contentId')
        );

        $output = ob_get_contents();
        ob_end_clean();

        return $this->json(json_decode($output, true));
    }

    #[Route('/files/')]

    /**
     * Callback for file uploads.
     *
     * @param Request $request Current request
     * @return JsonResponse
     */
    public function filesCallback(Request $request): JsonResponse
    {
        ob_start();

        $editor = $this->h5peditor;
        $id = $request->get('id') !== null ? $request->get('id') : $request->get('contentId');
        $editor->ajax->action(
            \H5PEditorEndpoints::FILES,
            $request->get('token', 1),
            $id
        );

        $output = ob_get_contents();
        ob_end_clean();

        return $this->json(json_decode($output, true));
    }

    #[Route('/filter/')]

    /**
     * Callback for filtering.
     *
     * @param Request $request Current request
     * @return JsonResponse
     */
    public function filterCallback(Request $request): JsonResponse
    {
        ob_start();

        $editor = $this->h5peditor;
        $editor->ajax->action(
            \H5PEditorEndpoints::FILTER,
            $request->get('token', 1),
            $request->get('libraryParameters')
        );

        $output = ob_get_contents();
        ob_end_clean();

        return $this->json(json_decode($output, true));
    }
}
