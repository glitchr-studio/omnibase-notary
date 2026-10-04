<?php

namespace Base\Notary;

use Base\Bundle\AbstractBaseBundle;
use Base\Traits\SingletonTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * The notarial regime, on omnibase/office: what a notary's office site owes
 * to its professional rules, written as checks the back office runs - the
 * regulated tariff and the discounts displayed, the consumer mediator of
 * the notariat, e-mail addresses ending in notaires.fr, the site declared
 * to the chamber, wording free of comparison - and its own pages: the
 * fields of practice, the tariff, the mediation, the client's space. No
 * online payment: the bundle has none.
 *
 * Compliance built into the code does not replace the chamber's control.
 */
class NotaryBundle extends AbstractBaseBundle
{
    use SingletonTrait;

    public function __construct()
    {
        parent::__construct();
    }

    /** Modern layout: the class lives in src/, the bundle root is the package root. */
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $this->setMapping($this->getPath().'/src/Entity', 'Base\Notary\Entity', 'App\Entity\Notary');
        $this->setMapping($this->getPath().'/src/Repository', 'Base\Notary\Repository', 'App\Repository\Notary');
    }
}
