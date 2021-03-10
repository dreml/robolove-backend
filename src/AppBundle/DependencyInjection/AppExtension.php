<?php
/**
 * Создано: 01.02.2018 Яковенко Никита <nyakovenko@htc-cs.ru>
 */

namespace AppBundle\DependencyInjection;

use JMS\SerializerBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\Routing\Loader\YamlFileLoader;

class AppExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
//        $loader = new YamlFileLoader(new FileLocator(__DIR__ . '/../Resources/config'));
//        $loader->load('services.yml');
    }
}