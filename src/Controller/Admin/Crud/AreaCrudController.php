<?php

namespace Base\Notary\Controller\Admin\Crud;

use Base\Admin\Config\Actions;
use Base\Admin\Config\Crud;
use Base\Admin\Controller\AbstractCrudController;
use Base\Field\AssociationField;
use Base\Field\BooleanField;
use Base\Field\IdField;
use Base\Field\IntegerField;
use Base\Field\SlugField;
use Base\Field\TextareaField;
use Base\Field\TextField;
use Base\Notary\Entity\Area;
use Base\Office\Controller\Admin\OpenToTrait;

/** The fields of practice as the site shows them. */
class AreaCrudController extends AbstractCrudController
{
    use OpenToTrait;

    public static function getEntityFqcn(): string
    {
        return Area::class;
    }

    public static function getPreferredIcon(): ?string
    {
        return 'fa-solid fa-scale-balanced';
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)->setDefaultSort(['position' => 'ASC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $this->openTo(parent::configureActions($actions), 'ROLE_ADMIN');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex();
        yield TextField::new('name', '@notary.admin.field.name')->setColumns(6);
        yield SlugField::new('slug')->setColumns(4)->hideOnIndex();
        yield IntegerField::new('position', '@notary.admin.field.position')->setColumns(2)->hideOnIndex();
        yield TextareaField::new('summary', '@notary.admin.field.summary')->setRequired(false)->hideOnIndex();
        yield TextareaField::new('body', '@notary.admin.field.body')->setRequired(false)->hideOnIndex()->setHelp('@notary.admin.help.body');
        yield TextareaField::new('deedsText', '@notary.admin.field.deeds')->setRequired(false)->hideOnIndex()->setHelp('@notary.admin.help.deeds');
        yield AssociationField::new('members', '@notary.admin.field.members')->setColumns(8)->setRequired(false)->hideOnIndex();
        yield BooleanField::new('active', '@notary.admin.field.active')->setColumns(2);
    }
}
