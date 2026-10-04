<?php

namespace Base\Notary\Twig;

use Base\Notary\Entity\Area;
use Base\Notary\Repository\AreaRepository;
use Base\Notary\Service\Record;
use Base\Office\Entity\Member;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * The regime in templates: notary_record() (the structure, the chamber, the
 * discounts typed in the settings), notary_areas(member|null) (the fields
 * of practice, or a member's), notary_mediator(), notary_tariff().
 */
class NotaryExtension extends AbstractExtension
{
    public function __construct(
        private readonly Record $record,
        private readonly AreaRepository $areas,
        #[Autowire('%notary.mediator%')] private readonly array $mediator = [],
        #[Autowire('%notary.tariff%')] private readonly array $tariff = [],
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('notary_record', fn (): Record => $this->record),
            /** @return list<Area> */
            new TwigFunction('notary_areas', fn (?Member $member = null): array => null === $member ? $this->areas->findActive() : $this->areas->findForMember($member)),
            new TwigFunction('notary_mediator', fn (): array => $this->mediator),
            new TwigFunction('notary_tariff', fn (): array => $this->tariff),
        ];
    }
}
