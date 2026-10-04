<?php

namespace Base\Notary\Compliance;

use Base\Notary\Service\Record;
use Base\Office\Compliance\ComplianceCheckInterface;
use Base\Office\Compliance\ComplianceResult;

/**
 * The profession's graphic charter and its brand block: the site keeps to
 * "la charte graphique [...] et le plan de nommage arrêtés par le Conseil
 * supérieur du notariat et publiés sur le portail intranet de la
 * profession" (règlement professionnel du notariat, art. 14.4), and the
 * brand block with the NOTAIRES DE FRANCE logo is put "sur tous les
 * supports de communication" (art. 14.5). Neither can be read from outside
 * the profession's intranet: the office says in the settings that it has
 * applied them, and until then the back office reminds it.
 */
final class BrandBlockCheck implements ComplianceCheckInterface
{
    public function __construct(private readonly Record $record)
    {
    }

    public function check(): ComplianceResult
    {
        return $this->record->isBrandApplied()
            ? ComplianceResult::ok('compliance.brand', 'notary')
            : new ComplianceResult('compliance.brand', ComplianceResult::WARNING, 'compliance.brand_advice', 'notary');
    }
}
