<?php

namespace Base\Notary\Entity;

use Base\Notary\Repository\AreaRepository;
use Base\Office\Entity\Member;
use Base\Office\Entity\Office;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * A field of practice of the office (family, real estate, businesses...):
 * what it covers, the deeds it gives rise to, who of the team follows it.
 * Information for the public - its wording is checked against comparison
 * (Base\Notary\Compliance\WordingCheck).
 */
#[ORM\Entity(repositoryClass: AreaRepository::class)]
#[ORM\Table(name: 'notary_area')]
class Area
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected $id;

    #[ORM\Column(length: 160)]
    protected string $name = '';

    #[ORM\Column(length: 160, unique: true)]
    protected string $slug = '';

    /** One or two sentences, for the list. */
    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $summary = null;

    /** The page's text: paragraphs separated by an empty line. */
    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $body = null;

    /** The deeds and steps of that field, as a list: "Vente", "Donation-partage"... */
    #[ORM\Column(type: 'json')]
    protected array $deeds = [];

    /** @var Collection<int, Member> who of the team follows it */
    #[ORM\ManyToMany(targetEntity: Member::class)]
    #[ORM\JoinTable(name: 'notary_area_member')]
    protected Collection $members;

    #[ORM\Column(type: 'boolean')]
    protected bool $active = true;

    #[ORM\Column(type: 'integer')]
    protected int $position = 0;

    public function __construct(string $name = '', ?string $slug = null)
    {
        $this->members = new ArrayCollection();
        $this->name = trim($name);
        $this->slug = Office::slugify($slug ?? $name);
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function setName(?string $name): self { $this->name = trim((string) $name); if ('' === $this->slug) { $this->slug = Office::slugify($this->name); } return $this; }
    public function getSlug(): string { return $this->slug; }
    public function setSlug(?string $slug): self { $this->slug = Office::slugify((string) $slug); return $this; }
    public function getSummary(): ?string { return $this->summary; }
    public function setSummary(?string $summary): self { $this->summary = $summary ?: null; return $this; }
    public function getBody(): ?string { return $this->body; }
    public function setBody(?string $body): self { $this->body = $body ?: null; return $this; }
    /** @return list<string> */
    public function getDeeds(): array { return $this->deeds; }
    public function setDeeds(?array $deeds): self { $this->deeds = array_values(array_filter(array_map('trim', (array) $deeds))); return $this; }
    /** The deeds, one a line: what the back office's form edits. */
    public function getDeedsText(): string { return implode("\n", $this->deeds); }
    public function setDeedsText(?string $text): self { return $this->setDeeds(preg_split('/\R/', (string) $text) ?: []); }
    /** @return Collection<int, Member> */
    public function getMembers(): Collection { return $this->members; }
    public function addMember(Member $member): self { if (!$this->members->contains($member)) { $this->members->add($member); } return $this; }
    public function removeMember(Member $member): self { $this->members->removeElement($member); return $this; }
    public function isActive(): bool { return $this->active; }
    public function setActive(bool $active): self { $this->active = $active; return $this; }
    public function getPosition(): int { return $this->position; }
    public function setPosition(?int $position): self { $this->position = (int) $position; return $this; }

    /** @return list<string> the body's paragraphs */
    public function getParagraphs(): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R{2,}/', (string) $this->body) ?: [])));
    }

    /** Everything written for the public, for the wording check. */
    public function getPublicText(): string
    {
        return implode("\n", array_filter([$this->name, $this->summary, $this->body, implode("\n", $this->deeds)]));
    }
}
