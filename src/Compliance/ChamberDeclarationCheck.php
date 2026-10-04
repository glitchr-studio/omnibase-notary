<?php

namespace Base\Notary\Compliance;

use Base\Notary\Service\Record;
use Base\Office\Compliance\ComplianceCheckInterface;
use Base\Office\Compliance\ComplianceResult;

/**
 * "L'office notarial qui ouvre un site internet, y propose ses services ou
 * ouvre ou modifie une ou plusieurs pages web destinées aux mêmes fins [...]
 * doit en informer sans délai la chambre des notaires dont il dépend"
 * (règlement professionnel du notariat, art. 14.4). The settings keep which
 * chamber and the day it was told.
 */
final class ChamberDeclarationCheck implements ComplianceCheckInterface
{
    public function __construct(private readonly Record $record)
    {
    }

    public function check(): ComplianceResult
    {
        if (null === $this->record->getChamber()) {
            return new ComplianceResult('compliance.chamber', ComplianceResult::MISSING, 'compliance.chamber_name_advice', 'notary');
        }
        $declared = $this->record->getDeclaredAt();
        if (null === $declared) {
            return new ComplianceResult('compliance.chamber', ComplianceResult::MISSING, 'compliance.chamber_advice', 'notary', ['chamber' => $this->record->getChamber()]);
        }
        if ($declared > new \DateTimeImmutable('tomorrow')) {
            return new ComplianceResult('compliance.chamber', ComplianceResult::WARNING, 'compliance.chamber_future', 'notary');
        }

        return ComplianceResult::ok('compliance.chamber', 'notary');
    }
}
