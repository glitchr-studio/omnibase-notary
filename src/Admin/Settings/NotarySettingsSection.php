<?php

namespace Base\Notary\Admin\Settings;

use Base\Admin\Settings\SettingsSectionInterface;
use Base\Notary\Service\Record;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The settings' notarial section: what the legal notice and the tariff page
 * print and only the office knows - the structure that holds the office,
 * its chamber and the day the site was declared to it, the discounts, the
 * fees for what the tariff does not cover. The compliance widget reads the
 * same.
 */
#[AsTaggedItem(priority: 40)]
final class NotarySettingsSection implements SettingsSectionInterface
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function getPage(): string
    {
        return self::SETTINGS;
    }

    public function getFields(): array
    {
        $label = fn (string $key): string => $this->translator->trans('settings.'.$key, [], 'notary');

        return [
            Record::HOLDER => ['required' => false, 'label' => $label('holder')],
            Record::CRPCEN => ['required' => false, 'label' => $label('crpcen')],
            Record::CHAMBER => ['required' => false, 'label' => $label('chamber')],
            Record::DECLARED_AT => ['required' => false, 'label' => $label('declared_at')],
            Record::BRAND => ['required' => false, 'label' => $label('brand')],
            Record::DISCOUNTS => ['required' => false, 'form_type' => TextareaType::class, 'label' => $label('discounts')],
            Record::FEES => ['required' => false, 'form_type' => TextareaType::class, 'label' => $label('fees')],
        ];
    }
}
