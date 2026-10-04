<?php

namespace Base\Notary\Compliance;

use Base\Office\Compliance\ComplianceCheckInterface;
use Base\Office\Compliance\ComplianceResult;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * "Le notaire doit faire connaître par tous moyens lisibles et appropriés
 * la possibilité pour le client d'avoir recours au médiateur de la
 * consommation du notariat en cas de différend entre eux et en indiquer les
 * coordonnées" (règlement professionnel du notariat, art. 25.1). The page
 * /mediation prints them: a name, and an address or a site.
 */
final class MediatorCheck implements ComplianceCheckInterface
{
    /** @param array{name?: ?string, address?: ?string, url?: ?string} $mediator */
    public function __construct(#[Autowire('%notary.mediator%')] private readonly array $mediator = [])
    {
    }

    public function check(): ComplianceResult
    {
        $name = trim((string) ($this->mediator['name'] ?? ''));
        $address = trim((string) ($this->mediator['address'] ?? ''));
        $url = trim((string) ($this->mediator['url'] ?? ''));

        if ('' === $name || ('' === $address && '' === $url)) {
            return new ComplianceResult('compliance.mediator', ComplianceResult::MISSING, 'compliance.mediator_advice', 'notary');
        }
        if ('' !== $url && 1 !== preg_match('~^https://~i', $url)) {
            return new ComplianceResult('compliance.mediator', ComplianceResult::WARNING, 'compliance.mediator_url', 'notary', ['url' => $url]);
        }

        return ComplianceResult::ok('compliance.mediator', 'notary');
    }
}
