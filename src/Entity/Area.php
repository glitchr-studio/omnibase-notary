<?php

namespace Base\Notary\Entity;

use Base\Notary\Repository\AreaRepository;
use Base\Office\Entity\Member;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * A field of practice of the office (family, real estate, businesses...):
 * what it covers, the deeds it gives rise to, who of the team follows it.
 * Information for the public - its wording is checked against comparison
 * (Base\Notary\Compliance\WordingCheck).
 *
 * The fields and the methods common to the regimes are omnibase/office's
 * (Base\Office\Entity\Area); the deeds and the table are the notaries'.
 */
#[ORM\Entity(repositoryClass: AreaRepository::class)]
#[ORM\Table(name: 'notary_area')]
class Area extends \Base\Office\Entity\Area
{
    /** The deeds and steps of that field, as a list: "Vente", "Donation-partage"... */
    #[ORM\Column(type: 'json')]
    protected array $deeds = [];

    /** @var Collection<int, Member> who of the team follows it */
    #[ORM\ManyToMany(targetEntity: Member::class)]
    #[ORM\JoinTable(name: 'notary_area_member')]
    protected Collection $members;

    /** @return list<string> */
    public function getDeeds(): array { return $this->deeds; }
    public function setDeeds(?array $deeds): self { $this->deeds = self::lines($deeds); return $this; }
    /** The deeds, one a line: what the back office's form edits. */
    public function getDeedsText(): string { return $this->getItemsText(); }
    public function setDeedsText(?string $text): self { return $this->setItemsText($text); }

    /** What omnibase/office's Area calls the list. */
    public function getItems(): array { return $this->deeds; }
    public function setItems(?array $items): static { return $this->setDeeds($items); }
}
