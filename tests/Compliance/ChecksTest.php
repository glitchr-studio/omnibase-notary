<?php

namespace Base\Notary\Tests\Compliance;

use Base\Notary\Compliance\BrandBlockCheck;
use Base\Notary\Compliance\ChamberDeclarationCheck;
use Base\Notary\Compliance\IdentificationCheck;
use Base\Notary\Compliance\MediatorCheck;
use Base\Notary\Compliance\ProfessionalEmailCheck;
use Base\Notary\Compliance\TariffDisplayCheck;
use Base\Notary\Compliance\WordingCheck;
use Base\Notary\Entity\Area;
use Base\Notary\Repository\AreaRepository;
use Base\Notary\Service\Record;
use Base\Office\Compliance\ComplianceResult;
use Base\Office\Entity\Member;
use Base\Office\Entity\Office;
use Base\Office\Repository\MemberRepository;
use Base\Office\Repository\OfficeRepository;
use Base\Service\SettingBagInterface;
use PHPUnit\Framework\TestCase;

/** Each rule of the regime as the back office's "Conformité" widget answers it. */
final class ChecksTest extends TestCase
{
    // ── Art. 16.3: addresses ending in notaires.fr ───────────────────

    public function testAnOfficeAddressOutsideNotairesFrIsMissing(): void
    {
        $result = (new ProfessionalEmailCheck($this->offices($this->office('contact@etude.example')), 'etude@notaires.fr'))->check();

        self::assertSame(ComplianceResult::MISSING, $result->status);
        self::assertSame('contact@etude.example', $result->parameters['addresses']);
        self::assertSame('notary', $result->domain);
    }

    public function testNoAddressAtAllIsMissing(): void
    {
        self::assertSame('compliance.email_none', (new ProfessionalEmailCheck($this->offices($this->office(null))))->check()->advice);
    }

    public function testTheSitesSenderIsOnlyAWarning(): void
    {
        $result = (new ProfessionalEmailCheck($this->offices($this->office('etude.dupont@notaires.fr')), 'noreply@site.example'))->check();

        self::assertSame(ComplianceResult::WARNING, $result->status);
        self::assertSame('compliance.email_sender', $result->advice);
    }

    public function testNotarialAddressesPass(): void
    {
        self::assertTrue((new ProfessionalEmailCheck($this->offices($this->office('etude.dupont@notaires.fr')), 'etude.dupont@notaires.fr'))->check()->isOk());
        self::assertTrue((new ProfessionalEmailCheck($this->offices($this->office('etude.dupont@notaires.fr')), null))->check()->isOk());
    }

    // ── Art. 14.4: the chamber is told ───────────────────────────────

    public function testTheSiteMustBeDeclaredToItsChamber(): void
    {
        self::assertSame('compliance.chamber_name_advice', (new ChamberDeclarationCheck($this->record([])))->check()->advice);

        $undated = (new ChamberDeclarationCheck($this->record([Record::CHAMBER => 'Chambre des notaires de la Côte-d’Or'])))->check();
        self::assertSame(ComplianceResult::MISSING, $undated->status);
        self::assertSame('Chambre des notaires de la Côte-d’Or', $undated->parameters['chamber']);

        self::assertSame(ComplianceResult::MISSING, (new ChamberDeclarationCheck($this->record([Record::CHAMBER => 'Chambre', Record::DECLARED_AT => 'bientôt'])))->check()->status, 'not a date');
        self::assertSame(ComplianceResult::WARNING, (new ChamberDeclarationCheck($this->record([Record::CHAMBER => 'Chambre', Record::DECLARED_AT => '2999-01-01'])))->check()->status);
        self::assertTrue((new ChamberDeclarationCheck($this->record([Record::CHAMBER => 'Chambre', Record::DECLARED_AT => '2026-09-14'])))->check()->isOk());
        self::assertTrue((new ChamberDeclarationCheck($this->record([Record::CHAMBER => 'Chambre', Record::DECLARED_AT => '14/09/2026'])))->check()->isOk());
    }

    // ── Art. 25.1: the mediator's coordinates ────────────────────────

    public function testTheMediatorsCoordinatesAreGiven(): void
    {
        self::assertTrue((new MediatorCheck(['name' => 'Médiateur de la consommation du notariat', 'address' => '60 boulevard de La Tour-Maubourg, 75007 Paris', 'url' => 'https://mediateur-notariat.notaires.fr']))->check()->isOk());
        self::assertTrue((new MediatorCheck(['name' => 'Médiateur', 'address' => 'Une adresse', 'url' => '']))->check()->isOk());
        self::assertSame(ComplianceResult::MISSING, (new MediatorCheck([]))->check()->status);
        self::assertSame(ComplianceResult::MISSING, (new MediatorCheck(['name' => 'Médiateur', 'address' => '', 'url' => null]))->check()->status);
        self::assertSame(ComplianceResult::WARNING, (new MediatorCheck(['name' => 'Médiateur', 'url' => 'http://mediateur.example']))->check()->status);
    }

    // ── Code de commerce, art. L. 444-4: tariff and discounts displayed ──

    public function testTheTariffPageLinksToTheOfficialTextAndPrintsTheDiscounts(): void
    {
        $tariff = ['code_url' => 'https://www.legifrance.gouv.fr/codes/texte_lc/LEGITEXT000005634379', 'order' => 'arrêté du 25 février 2026', 'order_url' => '', 'until' => '2028-02-29'];
        $today = new \DateTimeImmutable('2026-10-05');

        self::assertSame('compliance.tariff_discounts_advice', (new TariffDisplayCheck($this->record([]), $tariff, $today))->check()->advice);
        self::assertTrue((new TariffDisplayCheck($this->record([Record::DISCOUNTS => 'Aucune remise']), $tariff, $today))->check()->isOk());
        self::assertSame('compliance.tariff_link_advice', (new TariffDisplayCheck($this->record([Record::DISCOUNTS => 'Aucune remise']), ['code_url' => '', 'order_url' => ''], $today))->check()->advice);
    }

    public function testPastTheOrdersPeriodTheReferenceIsToBeUpdated(): void
    {
        $tariff = ['code_url' => 'https://example.org', 'order' => 'arrêté du 25 février 2026', 'until' => '2028-02-29'];
        $record = $this->record([Record::DISCOUNTS => 'Aucune remise']);

        self::assertTrue((new TariffDisplayCheck($record, $tariff, new \DateTimeImmutable('2028-02-29')))->check()->isOk(), 'its last day');
        $late = (new TariffDisplayCheck($record, $tariff, new \DateTimeImmutable('2028-03-01')))->check();
        self::assertSame(ComplianceResult::WARNING, $late->status);
        self::assertSame('29/02/2028', $late->parameters['date']);
    }

    // ── Art. 14.1, 14.2: wording ─────────────────────────────────────

    public function testComparativeWordingIsNamedWhereItIs(): void
    {
        $areas = $this->createStub(AreaRepository::class);
        $areas->method('findActive')->willReturn([(new Area('Immobilier'))->setSummary('Le meilleur office pour vendre.'), (new Area('Famille'))->setBody('Mariage, PACS, donation.')]);
        $members = $this->createStub(MemberRepository::class);
        $members->method('findVisible')->willReturn([(new Member('Me Jeanne Test'))->setBiography('Plus disponible que nos confrères.')]);

        $result = (new WordingCheck($areas, $members, $this->record([])))->check();

        self::assertSame(ComplianceResult::WARNING, $result->status);
        self::assertStringContainsString('Immobilier : « le meilleur »', $result->parameters['found']);
        self::assertStringContainsString('Me Jeanne Test', $result->parameters['found']);
        self::assertStringNotContainsString('Famille', $result->parameters['found']);
    }

    public function testNeutralWordingPasses(): void
    {
        $areas = $this->createStub(AreaRepository::class);
        $areas->method('findActive')->willReturn([(new Area('Famille'))->setBody('Mariage, PACS, donation.')]);
        $members = $this->createStub(MemberRepository::class);
        $members->method('findVisible')->willReturn([]);

        self::assertTrue((new WordingCheck($areas, $members, $this->record([Record::FEES => 'Consultation : sur convention.'])))->check()->isOk());
        self::assertSame(ComplianceResult::WARNING, (new WordingCheck($areas, $members, $this->record([Record::FEES => 'Honoraires imbattables'])))->check()->status);
    }

    // ── Art. 14.1 and LCEN art. 19: identification ───────────────────

    public function testTheOfficeIsIdentified(): void
    {
        $office = $this->office('etude@notaires.fr')->setStreet('4 place des Halles')->setCity('Sainte-Orlane')->setPhone('03 53 01 00 40');
        $full = $this->record([Record::HOLDER => 'SELARL Test, titulaire d’un office notarial', Record::CHAMBER => 'Chambre']);

        self::assertTrue((new IdentificationCheck($full, $this->offices($office)))->check()->isOk());

        $result = (new IdentificationCheck($this->record([]), $this->offices($this->office(null))))->check();
        self::assertSame(ComplianceResult::MISSING, $result->status);
        self::assertSame('holder, address, phone, chamber', $result->parameters['missing']);
    }

    // ── Art. 14.4, 14.5: charter and brand block ─────────────────────

    public function testTheCharterIsAReminderUntilTheOfficeConfirmsIt(): void
    {
        self::assertSame(ComplianceResult::WARNING, (new BrandBlockCheck($this->record([])))->check()->status);
        self::assertSame(ComplianceResult::WARNING, (new BrandBlockCheck($this->record([Record::BRAND => 'non'])))->check()->status);
        self::assertTrue((new BrandBlockCheck($this->record([Record::BRAND => 'Oui'])))->check()->isOk());
    }

    public function testASettingsTableNotThereYetAnswersNothing(): void
    {
        $settings = $this->createStub(SettingBagInterface::class);
        $settings->method('getScalar')->willThrowException(new \RuntimeException('no table'));
        $record = new Record($settings);

        self::assertNull($record->getHolder());
        self::assertNull($record->getDeclaredAt());
        self::assertFalse($record->isBrandApplied());
    }

    /** @param array<string, string> $values */
    private function record(array $values): Record
    {
        $settings = $this->createStub(SettingBagInterface::class);
        $settings->method('getScalar')->willReturnCallback(static fn ($path) => $values[$path] ?? null);

        return new Record($settings);
    }

    private function office(?string $email): Office
    {
        return (new Office('Étude', 'etude'))->setEmail($email);
    }

    private function offices(Office ...$offices): OfficeRepository
    {
        $repository = $this->createStub(OfficeRepository::class);
        $repository->method('findOrdered')->willReturn($offices);
        $repository->method('findMain')->willReturn($offices[0] ?? null);

        return $repository;
    }
}
