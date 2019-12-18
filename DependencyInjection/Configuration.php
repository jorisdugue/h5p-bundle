<?php

namespace Studit\H5PBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\NodeInterface;

class Configuration implements ConfigurationInterface
{
    const H5P_VERSION = '0.1'; // version of Symfony H5P bundle

    /**
     * Generates the configuration tree.
     *
     * @return NodeInterface
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('studit_h5_p');

        if (method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config 4.1 and older
            $rootNode = $treeBuilder->root('studit_h5_p');
        }
        $rootNode
            ->children()
            ->scalarNode('storage_dir')->defaultValue("h5p")->end()
            ->scalarNode('web_dir')->defaultValue("public")->end()
            ->booleanNode('hub_is_enabled')->defaultTrue()->end()
            ->booleanNode('send_usage_statistics')->defaultTrue()->end()
            ->booleanNode('save_content_state')->defaultFalse()->end()
            ->integerNode('save_content_fequency')->defaultValue(30)->end()
            ->scalarNode('whitelist')->defaultValue(\H5PCore::$defaultContentWhitelist)->end()
            ->scalarNode('library_whitelist_extras')->defaultValue(\H5PCore::$defaultLibraryWhitelistExtras)->end()
            ->booleanNode('dev_mode')->defaultFalse()->end()
            ->booleanNode('first_runnable_saved')->defaultFalse()->end()
            ->scalarNode('site_type')->defaultValue('local')->end()
            ->scalarNode('site_uuid')->defaultValue('')->end()
            ->booleanNode('send_usage_statistics')->defaultTrue()->end()
            ->booleanNode(\H5PCore::DISPLAY_OPTION_ABOUT)->defaultTrue()->end()
            ->booleanNode(\H5PCore::DISPLAY_OPTION_FRAME)->defaultTrue()->end()
            ->integerNode(\H5PCore::DISPLAY_OPTION_DOWNLOAD)->defaultValue(\H5PDisplayOptionBehaviour::NEVER_SHOW)->end()
            ->integerNode(\H5PCore::DISPLAY_OPTION_EMBED)->defaultValue(\H5PDisplayOptionBehaviour::NEVER_SHOW)->end()
            ->booleanNode(\H5PCore::DISPLAY_OPTION_COPYRIGHT)->defaultTrue()->end()
            ->integerNode('content_type_cache_updated_at')->defaultValue(0)->end()
            ->booleanNode('enable_lrs_content_types')->defaultFalse()->end()
            ->booleanNode('use_permission')->defaultFalse()->end()
            ->end();
        return $treeBuilder;
    }
}