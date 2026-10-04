<?php

namespace Base\Notary\Tests\Guard;

use Base\Notary\Guard\Wording;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** No comparison, no disparagement, none of advertising's superlatives (règlement professionnel, art. 14.1 and 14.2; decree n° 73-1202 as amended). */
final class WordingTest extends TestCase
{
    #[DataProvider('forbidden')]
    public function testComparisonAndAdvertisingAreFound(string $text, string $label): void
    {
        self::assertContains($label, Wording::scan($text), $text);
    }

    public static function forbidden(): iterable
    {
        yield ['Le meilleur notaire du canton.', 'le meilleur'];
        yield ['Nos tarifs sont MEILLEURS QUE ceux d’à côté', 'meilleur que'];
        yield ['Office n°1 de la région', 'numéro 1'];
        yield ['L’étude numéro un des successions', 'numéro 1'];
        yield ['Des honoraires moins chers.', 'moins cher'];
        yield ['Plus réactifs que nos confrères', 'plus … que nos confrères'];
        yield ['Contrairement à d’autres études, nous répondons.', 'contrairement à'];
        yield ['Un savoir-faire <em>inégalé</em>', 'incomparable'];
        yield ['Résultat garanti', 'résultat garanti'];
        yield ['Offre spéciale de rentrée', 'offre spéciale'];
    }

    #[DataProvider('allowed')]
    public function testThePlainWordsOfTheTradeAreNot(string $text): void
    {
        self::assertSame([], Wording::scan($text), $text);
    }

    public static function allowed(): iterable
    {
        yield ['Vente en l’état futur d’achèvement et promotion immobilière.'];
        yield ['Nous vous répondons dans les meilleurs délais.'];
        yield ['Droit de la concurrence et de la distribution.'];
        yield ['L’office accompagne les familles depuis 1987.'];
        yield ['Le bail commercial est plus protecteur que le bail dérogatoire.'];
        yield [''];
    }

    public function testTheSitesOwnExpressionsAreAdded(): void
    {
        self::assertSame(['Étude de référence'], Wording::scan('L’étude de référence du Val.', ['Étude de référence']));
        self::assertSame([], Wording::scan('Les références cadastrales.', ['Étude de référence']));
        self::assertTrue(Wording::isClean('Rien à signaler.'));
    }

    public function testEachExpressionIsReportedOnce(): void
    {
        self::assertSame(['numéro 1'], Wording::scan('N°1 ici, numéro 1 là.'));
    }
}
