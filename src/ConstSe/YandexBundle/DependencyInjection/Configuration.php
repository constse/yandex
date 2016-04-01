<?php

namespace ConstSe\YandexBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Constantine Seleznyoff <const.seoff@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();
        $root = $builder->root('yandex');

        $root
            ->children()
                ->append($this->getDirectNode())
            ->end();

        return $builder;
    }

    /**
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition
     */
    protected function getDirectNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('direct');

        $node
            ->children()
                ->scalarNode('token')
                    ->isRequired()
                ->end()
            ->end();

        return $node;
    }
}
