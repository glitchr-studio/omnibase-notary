<?php

namespace Base\Notary\Compliance;

use Base\Notary\Guard\Wording;
use Base\Notary\Repository\AreaRepository;
use Base\Notary\Service\Record;
use Base\Office\Compliance\ComplianceCheckInterface;
use Base\Office\Compliance\ComplianceResult;
use Base\Office\Repository\MemberRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * What the office wrote for the public - the fields of practice, the
 * team's biographies, its fees - read again for comparison, disparagement
 * and the superlatives of advertising (Base\Notary\Guard\Wording says on
 * which texts this rests). A warning names where and what.
 */
final class WordingCheck implements ComplianceCheckInterface
{
    /** @param list<string> $extra */
    public function __construct(
        private readonly AreaRepository $areas,
        private readonly MemberRepository $members,
        private readonly Record $record,
        #[Autowire('%notary.wording%')] private readonly array $extra = [],
    ) {
    }

    public function check(): ComplianceResult
    {
        $found = [];
        foreach ($this->areas->findActive() as $area) {
            $this->collect($found, $area->getName(), $area->getPublicText());
        }
        foreach ($this->members->findVisible() as $member) {
            $this->collect($found, $member->getDisplayName(), $member->getTitle()."\n".$member->getBiography());
        }
        $this->collect($found, 'Réglages', $this->record->getFees()."\n".$this->record->getDiscounts());

        return [] === $found
            ? ComplianceResult::ok('compliance.wording', 'notary')
            : new ComplianceResult('compliance.wording', ComplianceResult::WARNING, 'compliance.wording_advice', 'notary', ['found' => implode(' ; ', $found)]);
    }

    /** @param list<string> $found */
    private function collect(array &$found, string $where, ?string $text): void
    {
        $matches = Wording::scan($text, $this->extra);
        if ([] !== $matches) {
            $found[] = sprintf('%s : « %s »', $where, implode(' », « ', $matches));
        }
    }
}
