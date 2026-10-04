<?php

namespace Base\Notary\Compliance;

use Base\Notary\Guard\EmailDomain;
use Base\Office\Compliance\ComplianceCheckInterface;
use Base\Office\Compliance\ComplianceResult;
use Base\Office\Repository\OfficeRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * The office's e-mail addresses end with notaires.fr (règlement
 * professionnel du notariat, art. 16.3): the address each office shows to
 * the public, and the one the site writes to clients from.
 */
final class ProfessionalEmailCheck implements ComplianceCheckInterface
{
    public function __construct(
        private readonly OfficeRepository $offices,
        #[Autowire('%office.sender%')] private readonly ?string $sender = null,
        #[Autowire('%notary.email_suffix%')] private readonly string $suffix = 'notaires.fr',
    ) {
    }

    public function check(): ComplianceResult
    {
        $wrong = [];
        $any = false;
        foreach ($this->offices->findOrdered() as $office) {
            if (null === $office->getEmail()) {
                continue;
            }
            $any = true;
            if (!EmailDomain::isNotarial($office->getEmail(), $this->suffix)) {
                $wrong[] = $office->getEmail();
            }
        }
        if ([] !== $wrong) {
            return new ComplianceResult('compliance.email', ComplianceResult::MISSING, 'compliance.email_advice', 'notary', ['addresses' => implode(', ', array_unique($wrong)), 'suffix' => $this->suffix]);
        }
        if (!$any) {
            return new ComplianceResult('compliance.email', ComplianceResult::MISSING, 'compliance.email_none', 'notary', ['suffix' => $this->suffix]);
        }
        if (null !== $this->sender && '' !== trim($this->sender) && !EmailDomain::isNotarial($this->sender, $this->suffix)) {
            return new ComplianceResult('compliance.email', ComplianceResult::WARNING, 'compliance.email_sender', 'notary', ['addresses' => $this->sender, 'suffix' => $this->suffix]);
        }

        return ComplianceResult::ok('compliance.email', 'notary');
    }
}
