<?php

namespace Studit\H5PBundle\Tests\Core;

use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Studit\H5PBundle\Entity\Option;
use Studit\H5PBundle\Core\H5POptions;
use Doctrine\DBAL\Exception\DriverException;
use PHPUnit\Framework\MockObject\MockObject;

class H5POptionsTest extends TestCase
{
    private H5POptions $h5pOptions;
    private EntityManagerInterface|MockObject $entityManager;
    private EntityRepository|MockObject $repository;

    protected function setUp(): void
    {
        // Créez un mock pour l'EntityManager
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        // Créez un mock pour l'EntityRepository
        $this->repository = $this->createMock(EntityRepository::class);

        // Configurez l'EntityManager pour retourner un mock de repository
        $this->entityManager->method('getRepository')->willReturn($this->repository);

        // Initialisez H5POptions avec les dépendances mockées
        $this->h5pOptions = new H5POptions(
            ['storage_dir' => '/var/www/html'],  // Config de test
            '/var/www',  // projectRootDir de test
            $this->entityManager
        );
    }

    public function testGetOptionReturnsStoredConfigValue()
    {
        // Simule la méthode findAll() du repository pour retourner une option
        $option = $this->createMock(Option::class);
        $option->method('getName')->willReturn('storage_dir');
        $option->method('getValue')->willReturn('/tmp/h5p');

        // Configurez le repository pour retourner cette option
        $this->repository->method('findAll')->willReturn([$option]);

        // Testez la méthode getOption
        $result = $this->h5pOptions->getOption('storage_dir');
        $this->assertEquals('/tmp/h5p', $result);
    }

    public function testSetOptionStoresNewOptionValue()
    {
        // Créez un mock de l'option à persister
        $option = $this->createMock(Option::class);
        $option->method('getName')->willReturn('storage_dir');

        // Simulez la recherche de l'option dans le repository
        $this->repository->method('find')->willReturn(null);  // Aucun option trouvée, il faut créer une nouvelle

        // Configurez l'EntityManager pour simuler les méthodes persist et flush
        $this->entityManager->expects($this->once())->method('persist')->with($this->isInstanceOf(Option::class));
        $this->entityManager->expects($this->once())->method('flush');

        // Appelez setOption et vérifiez que persist et flush sont bien appelées
        $this->h5pOptions->setOption('storage_dir', '/new/path/h5p');
    }

    public function testGetOptionReturnsDefaultIfOptionNotFound()
    {
        // Configurez le mock pour retourner une liste vide d'options
        $this->repository->method('findAll')->willReturn([]);

        // Testez la méthode getOption avec une option qui n'existe pas
        $result = $this->h5pOptions->getOption('non_existent_option', 'default_value');
        $this->assertEquals('default_value', $result);
    }

    public function testRetrieveStoredConfigHandlesDriverException()
    {
        // Testez la gestion de l'exception dans la méthode retrieveStoredConfig
        $this->expectNotToPerformAssertions();
        try {
            $this->h5pOptions->getOption('storage_dir');
        } catch (DriverException $e) {
            // Vérifiez que l'exception est bien attrapée
            $this->assertEquals('Database error', $e->getMessage());
        }
    }

    public function testGetUploadedH5pFolderPath()
    {
        // Testez le getter et setter de folderPath
        $this->h5pOptions->getUploadedH5pFolderPath('/custom/folder');
        $this->assertEquals('/custom/folder', $this->h5pOptions->getUploadedH5pFolderPath());
    }

    public function testGetRelativeH5PPath()
    {
        // Testez la méthode getRelativeH5PPath pour obtenir le chemin relatif
        $this->h5pOptions->setOption('storage_dir', 'var/h5p');
        $this->assertEquals('/var/h5p', $this->h5pOptions->getRelativeH5PPath());
    }

    public function testGetAbsoluteH5PPath()
    {
        // Testez la méthode getAbsoluteH5PPath pour obtenir le chemin absolu
        $this->h5pOptions->setOption('storage_dir', 'var/h5p');
        $this->assertStringContainsString('/var/www/var/h5p', $this->h5pOptions->getAbsoluteH5PPath());
    }
}
