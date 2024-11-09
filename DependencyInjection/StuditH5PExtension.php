<?php

namespace Studit\H5PBundle\DependencyInjection;

use Exception;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;

class StuditH5PExtension extends Extension
{
    /**
     * @inheritDoc
     * @throws Exception
     * @return void
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
        $definition = $container->getDefinition("studit_h5p.options");
        $definition->setArgument(0, $config);

        // stop saving the .h5p files if export/frame false
        if (!$config['frame'] || !$config['export']) {
            $definition = $container->getDefinition("studit_h5p.core");
            $definition->setArgument(4, false);
        }
    }
}
