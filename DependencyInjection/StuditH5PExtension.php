<?php

namespace Studit\H5PBundle\DependencyInjection;

use Exception;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;


class StuditH5PExtension extends Extension
{

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
        /** @var $definition Definition **/
        $definition = $container->getDefinition("studit_h5p.core");
        $definition->setArgument(1, $container->getParameter('kernel.project_dir') . '/' . $config['web_dir'] . '/' . $config["storage_dir"]);
        $definition->setArgument(2, '/');
        $definition->setArgument(3, 'en');
        $definition->setArgument(4, true);
        $definition = $container->getDefinition("studit_h5p.options");
        $definition->setArgument(0, $config);
    }
}