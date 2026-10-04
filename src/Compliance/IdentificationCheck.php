<?php

namespace Base\Notary\Compliance;

use Base\Notary\Service\Record;
use Base\Office\Compliance\ComplianceCheckInterface;
use Base\Office\Compliance\ComplianceResult;
use Base\Office\Repository\OfficeRepository;

/**
 * The office can be identified: "Tout document destiné à la correspondance
 * ou à la communication du notaire doit mentionner les éléments permettant
 * de l'identifier" (règlement professionnel du notariat, art. 14.1), and a
 * member of a regulated profession names on its site its professional
 * title, the body it is registered with and the rules that apply to it
 * (loi n° 2004-575 du 21 juin 2004, art. 19). The legal notice prints the
 * structure that holds the office, its address, its chamber.
 */
final class IdentificationCheck implements ComplianceCheckInterface
{
    public function __construct(private readonly Record $record, private readonly OfficeRepository $offices)
    {
    }

    public function check(): ComplianceResult
    {
        $office = $this->offices->findMain();
        $missing = [];
        if (null === $this->record->getHolder()) {
            $missing[] = 'holder';
        }
        if (null === $office || null === $office->getStreet() || null === $office->getCity()) {
            $missing[] = 'address';
        }
        if (null === $office || null === $office->getPhone()) {
            $missing[] = 'phone';
        }
        if (null === $this->record->getChamber()) {
            $missing[] = 'chamber';
        }

        return [] === $missing
            ? ComplianceResult::ok('compliance.identification', 'notary')
            : new ComplianceResult('compliance.identification', ComplianceResult::MISSING, 'compliance.identification_advice', 'notary', ['missing' => implode(', ', $missing)]);
    }
}
