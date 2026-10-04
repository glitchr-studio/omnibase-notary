<?php

namespace Base\Notary\DependencyInjection;

use Base\Bundle\AbstractBaseConfiguration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class NotaryConfiguration extends AbstractBaseConfiguration
{
    private bool $childrenDeclared = false;

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = $this->getTreeBuilder();
        if ($this->childrenDeclared) {
            return $treeBuilder;
        }
        $this->childrenDeclared = true;

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('roles')->addDefaultsIfNotSet()
                    ->info('The roles omnibase\'s groups give (security.yaml puts the first two under the third).')
                    ->children()
                        ->scalarNode('notary')->defaultValue('ROLE_NOTARY')->end()
                        ->scalarNode('clerk')->defaultValue('ROLE_CLERK')->end()
                        ->scalarNode('staff')->defaultValue('ROLE_STAFF')->end()
                    ->end()
                ->end()
                ->scalarNode('email_suffix')->defaultValue('notaires.fr')
                    ->info('What a notary\'s professional e-mail address ends with (règlement professionnel, art. 16.3).')->end()
                ->arrayNode('mediator')->addDefaultsIfNotSet()
                    ->info('The consumer mediator of the notariat, whose coordinates the office must give (règlement professionnel, art. 25.1).')
                    ->children()
                        ->scalarNode('name')->defaultValue('Médiateur de la consommation du notariat')->end()
                        ->scalarNode('address')->defaultValue('60 boulevard de La Tour-Maubourg, 75007 Paris')->end()
                        ->scalarNode('url')->defaultValue('https://mediateur-notariat.notaires.fr')->end()
                    ->end()
                ->end()
                ->arrayNode('tariff')->addDefaultsIfNotSet()
                    ->info('Where the regulated tariff is read: the official text, the order in force, the profession\'s explanation.')
                    ->children()
                        ->scalarNode('code_url')->defaultValue('https://www.legifrance.gouv.fr/codes/texte_lc/LEGITEXT000005634379')
                            ->info('Code de commerce, articles A444-53 and following (the notaries\' tariff).')->end()
                        ->scalarNode('order')->defaultValue('arrêté du 25 février 2026')
                            ->info('The order fixing the tariff in force, as it is named on the page.')->end()
                        ->scalarNode('order_url')->defaultValue('https://www.legifrance.gouv.fr/eli/arrete/2026/2/25/ECOC2604872A/jo/texte')->end()
                        ->scalarNode('until')->defaultValue('2028-02-29')
                            ->info('The last day of the order\'s period: past it, the back office asks for the new order.')->end()
                        ->scalarNode('info_url')->defaultValue('https://www.notaires.fr/fr/profession-notaire/le-tarif-du-notaire-emoluments-et-honoraires')->end()
                    ->end()
                ->end()
                ->arrayNode('wording')
                    ->info('Expressions added to the built-in list the texts are checked against (comparison, disparagement).')
                    ->scalarPrototype()->end()
                    ->defaultValue([])
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
