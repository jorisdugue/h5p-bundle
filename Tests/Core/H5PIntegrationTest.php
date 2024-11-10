<?php

namespace Studit\H5PBundle\Tests\Core;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use H5PCore;
use H5peditor;
use H5PContentValidator;
use H5PFrameworkInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Studit\H5PBundle\Core\H5PIntegration;
use Studit\H5PBundle\Core\H5POptions;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class H5PIntegrationTest extends TestCase
{
    private H5PIntegration|MockObject $h5pIntegration;
    private H5POptions|MockObject $options;
    private H5PCore|MockObject $core;
    private EntityManagerInterface|MockObject $entityManager;
    private RouterInterface|MockObject $router;
    private RequestStack|MockObject $requestStack;
    private Packages|MockObject $assetsPaths;
    private H5PContentValidator|MockObject $contentValidator;

    protected function setUp(): void
    {
        $this->core = $this->createMock(H5PCore::class);
        $this->options = $this->createMock(H5POptions::class);
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->entityManager = $this->createMock(EntityManager::class);
        $this->router = $this->createMock(RouterInterface::class);
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->assetsPaths = $this->createMock(Packages::class);
        $this->contentValidator = $this->createMock(H5PContentValidator::class);

        // Création de l'instance de H5PIntegration pour les tests
        $this->h5pIntegration = new H5PIntegration(
            $this->core,
            $this->options,
            $tokenStorage,
            $this->entityManager,
            $this->router,
            $this->requestStack,
            $this->assetsPaths,
            $this->contentValidator
        );
    }

    public function testGetGenericH5PIntegrationSettings()
    {
        $request = new Request();
        $this->requestStack->method('getMainRequest')->willReturn($request);

        $this->options->method('getOption')->willReturnMap([
            ['save_content_state', false, true],
            ['save_content_frequency', 30, 30],
            ['hub_is_enabled', true, true]
        ]);
        $h5pFrameworkMock = $this->createMock(H5PFrameworkInterface::class);
        $h5pFrameworkMock->method('getLibraryConfig')->willReturn(['someKey' => 'someValue']);

        // Injectez le mock H5PFramework dans H5PCore
        $this->core->h5pF = $h5pFrameworkMock;

        $settings = $this->h5pIntegration->getGenericH5PIntegrationSettings();

        $this->assertIsArray($settings);
        $this->assertArrayHasKey('baseUrl', $settings);
        $this->assertArrayHasKey('ajax', $settings);
        $this->assertArrayHasKey('l10n', $settings);
    }

    public function testGetCoreAssets()
    {
        $this->options->method('getH5PAssetPath')->willReturn('/assets/h5p');
        H5PCore::$scripts = ['script1.js', 'script2.js'];
        H5PCore::$styles = ['style1.css', 'style2.css'];

        $assets = $this->h5pIntegration->getCoreAssets();

        $this->assertIsArray($assets);
        $this->assertArrayHasKey('scripts', $assets);
        $this->assertArrayHasKey('styles', $assets);
        $this->assertCount(2, $assets['scripts']);
        $this->assertCount(2, $assets['styles']);
    }

    public function testGetCacheBuster()
    {
        H5PCore::$coreApi = ['majorVersion' => 1, 'minorVersion' => 2];
        $cacheBuster = $this->h5pIntegration->getCacheBuster();

        $this->assertEquals('?=1.2', $cacheBuster);
    }

    public function testGetTranslationFilePath()
    {
        $request = new Request();
        $request->setLocale('en');
        $this->requestStack->method('getCurrentRequest')->willReturn($request);

        $this->options->method('getAbsoluteWebPath')->willReturn('/web');

        $translationFilePath = $this->h5pIntegration->getTranslationFilePath();

        $this->assertStringContainsString('/h5p-editor/language/en.js', $translationFilePath);
    }

}
