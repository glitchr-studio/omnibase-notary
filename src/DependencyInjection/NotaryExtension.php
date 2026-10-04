<?php

namespace Base\Notary\DependencyInjection;

use Base\Bundle\AbstractBaseExtension;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class NotaryExtension extends AbstractBaseExtension implements PrependExtensionInterface
{
    public function getConfiguration(array $config, ContainerBuilder $container): NotaryConfiguration
    {
        return new NotaryConfiguration();
    }

    /**
     * The office's pages in the notarial regime's words: the contact form's
     * warning (nothing confidential here: the client's space is for that),
     * the menu of the client's space, where the e-mails send the client.
     */
    public function prepend(ContainerBuilder $container): void
    {
        if ($container->hasExtension('office')) {
            $container->prependExtensionConfig('office', [
                'templates' => ['space_nav' => '@Notary/client/space/_nav.html.twig'],
                'contact' => ['notice' => '@notary.contact.notice'],
                'booking' => ['space_route' => 'notary_space'],
            ]);
        }
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new NotaryConfiguration();
        $config = (new Processor())->processConfiguration($configuration, $configs);
        $this->setConfiguration($container, $config, $configuration->getTreeBuilder()->buildTree()->getName());

        $loader = new PhpFileLoader($container, new FileLocator(\dirname(__DIR__, 2).'/config'));
        $loader->load('services.php');
    }
}
