<?php

namespace Studit\H5PBundle\DependencyInjection;

use RuntimeException;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\NodeInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * version of Symfony H5P bundle
     * @return string
     */
    const H5P_VERSION = '2.2.1';

    /**
     * Generates the configuration tree.
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('studit_h5_p');

        $rootNode = $treeBuilder->getRootNode();
        if (!method_exists($rootNode, 'children')) {
            throw new RuntimeException(
                'Your Symfony version does not support the children() method to define the root node in' .
                ' the H5P bundle configuration.'
            );
        }

        $rootNode
            ->children()
            ->scalarNode('storage_dir')->defaultValue("h5p")->end()
            ->scalarNode('web_dir')->defaultValue("public")->end()
            ->booleanNode('hub_is_enabled')->defaultTrue()->end()
            ->booleanNode('send_usage_statistics')->defaultTrue()->end()
            ->booleanNode('save_content_state')->defaultFalse()->end()
            ->integerNode('save_content_frequency')->defaultValue(30)->end()
            ->scalarNode('whitelist')->defaultValue(\H5PCore::$defaultContentWhitelist)->end()
            ->scalarNode('library_whitelist_extras')->defaultValue(\H5PCore::$defaultLibraryWhitelistExtras)->end()
            ->booleanNode('dev_mode')->defaultFalse()->end()
            ->booleanNode('first_runnable_saved')->defaultFalse()->end()
            ->scalarNode('site_type')->defaultValue('local')->end()
            ->scalarNode('site_uuid')->defaultValue('')->end()
            ->booleanNode('send_usage_statistics')->defaultTrue()->end()
            ->booleanNode(\H5PCore::DISPLAY_OPTION_ABOUT)->defaultTrue()->end()
            ->booleanNode(\H5PCore::DISPLAY_OPTION_FRAME)->defaultTrue()->end()
            ->integerNode(\H5PCore::DISPLAY_OPTION_DOWNLOAD)
            ->defaultValue(\H5PDisplayOptionBehaviour::NEVER_SHOW)
            ->end()
            ->integerNode(\H5PCore::DISPLAY_OPTION_EMBED)->defaultValue(\H5PDisplayOptionBehaviour::NEVER_SHOW)->end()
            ->booleanNode(\H5PCore::DISPLAY_OPTION_COPY)->defaultValue(\H5PDisplayOptionBehaviour::NEVER_SHOW)->end()
            ->booleanNode(\H5PCore::DISPLAY_OPTION_COPYRIGHT)->defaultTrue()->end()
            ->integerNode('content_type_cache_updated_at')->defaultValue(0)->end()
            ->booleanNode('enable_lrs_content_types')->defaultFalse()->end()
            ->booleanNode('use_permission')->defaultFalse()->end()
            ->end();
        return $treeBuilder;
    }
}
