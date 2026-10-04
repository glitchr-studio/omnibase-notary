<?php

namespace Base\Notary\Service;

use Base\Service\SettingBagInterface;

/**
 * What only the office knows and the back office's settings keep: the
 * structure that holds the office, its chamber, the day the site was
 * declared to it, the discounts it practises, its fees for what the tariff
 * does not cover. Read without ever failing a page: a settings table not
 * there yet answers nothing.
 */
class Record
{
    public const HOLDER = 'notary.office.holder';
    public const CRPCEN = 'notary.office.crpcen';
    public const CHAMBER = 'notary.chamber.name';
    public const DECLARED_AT = 'notary.chamber.declared_at';
    public const BRAND = 'notary.brand.applied';
    public const DISCOUNTS = 'notary.tariff.discounts';
    public const FEES = 'notary.tariff.fees';

    public function __construct(private readonly SettingBagInterface $settings)
    {
    }

    public function get(string $path): ?string
    {
        try {
            $value = $this->settings->getScalar($path);
        } catch (\Throwable) {
            return null;
        }
        $value = \is_scalar($value) ? trim((string) $value) : '';

        return '' === $value ? null : $value;
    }

    public function getHolder(): ?string { return $this->get(self::HOLDER); }
    public function getCrpcen(): ?string { return $this->get(self::CRPCEN); }
    public function getChamber(): ?string { return $this->get(self::CHAMBER); }
    public function getDiscounts(): ?string { return $this->get(self::DISCOUNTS); }
    public function getFees(): ?string { return $this->get(self::FEES); }

    /** The day the site was declared to the chamber, when what was typed is a date. */
    public function getDeclaredAt(): ?\DateTimeImmutable
    {
        $value = $this->get(self::DECLARED_AT);
        if (null === $value) {
            return null;
        }
        foreach (['!Y-m-d', '!d/m/Y', '!d.m.Y'] as $format) {
            $date = \DateTimeImmutable::createFromFormat($format, $value);
            if (false !== $date && $date->format(ltrim($format, '!')) === $value) {
                return $date;
            }
        }

        return null;
    }

    public function isBrandApplied(): bool
    {
        return \in_array(mb_strtolower((string) $this->get(self::BRAND)), ['1', 'oui', 'yes', 'true', 'on'], true);
    }
}
