<?php

namespace ConstSe\YandexBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @author Constantine Seleznyoff <const.seoff@gmail.com>
 */
class YandexExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);
        $this->registerYandexConfiguration($config, $container);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
    }

    /**
     * @param array $config
     * @param ContainerBuilder $container
     * @return void
     */
    protected function registerYandexConfiguration(array $config, ContainerBuilder $container)
    {
        $container->setParameter('yandex.direct.token', $config['direct'], $config['token']);
    }
}
