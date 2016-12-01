<?php

namespace Pimcore;

use Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle;
use Symfony\Bundle\DebugBundle\DebugBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;

abstract class Kernel extends \DI\Bridge\Symfony\Kernel
{
    public function registerBundles()
    {
        $bundles = [
            new FrameworkBundle(),
            new SensioFrameworkExtraBundle()
        ];

        if (in_array($this->getEnvironment(), ['dev', 'test'], true)) {
            $bundles[] = new DebugBundle();
            // $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
        }

        return $bundles;
    }

    public function getRootDir()
    {
        return PIMCORE_WEBSITE_PATH;
    }

    public function getCacheDir()
    {
        return PIMCORE_CACHE_DIRECTORY . '/symfony/' . $this->getEnvironment();
    }

    public function getLogDir()
    {
        return PIMCORE_LOG_DIRECTORY . '/symfony';
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir() . '/var/config/symfony/config_' . $this->getEnvironment() . '.yml');
    }

    protected function buildPHPDIContainer(\DI\ContainerBuilder $builder)
    {
        \Pimcore::addDiDefinitions($builder);

        return $builder->build();
    }
}
