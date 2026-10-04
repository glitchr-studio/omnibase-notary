<?php

namespace Base\Notary\Compliance;

use Base\Notary\Service\Record;
use Base\Office\Compliance\ComplianceCheckInterface;
use Base\Office\Compliance\ComplianceResult;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * The tariff is displayed on the site (code de commerce, art. L. 444-4: the
 * notaries post the tariffs they practise in their office and on their
 * website), and with it the discounts the office grants, by category of
 * deeds and by band - or that it grants none. The page /tarifs links to the
 * official text; the office types its discounts in the settings. Past the
 * period of the order in force, the reference is to be brought up to date.
 */
final class TariffDisplayCheck implements ComplianceCheckInterface
{
    /** @param array{code_url?: ?string, order?: ?string, order_url?: ?string, until?: ?string} $tariff */
    public function __construct(
        private readonly Record $record,
        #[Autowire('%notary.tariff%')] private readonly array $tariff = [],
        private readonly ?\DateTimeImmutable $today = null,
    ) {
    }

    public function check(): ComplianceResult
    {
        if ('' === trim((string) ($this->tariff['code_url'] ?? '')) && '' === trim((string) ($this->tariff['order_url'] ?? ''))) {
            return new ComplianceResult('compliance.tariff', ComplianceResult::MISSING, 'compliance.tariff_link_advice', 'notary');
        }
        if (null === $this->record->getDiscounts()) {
            return new ComplianceResult('compliance.tariff', ComplianceResult::MISSING, 'compliance.tariff_discounts_advice', 'notary');
        }
        $until = trim((string) ($this->tariff['until'] ?? ''));
        $last = '' !== $until ? \DateTimeImmutable::createFromFormat('!Y-m-d', $until) : false;
        if (false !== $last && ($this->today ?? new \DateTimeImmutable('today')) > $last) {
            return new ComplianceResult('compliance.tariff', ComplianceResult::WARNING, 'compliance.tariff_outdated', 'notary', ['order' => (string) ($this->tariff['order'] ?? ''), 'date' => $last->format('d/m/Y')]);
        }

        return ComplianceResult::ok('compliance.tariff', 'notary');
    }
}
