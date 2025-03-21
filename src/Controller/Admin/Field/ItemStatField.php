<?php

namespace App\Controller\Admin\Field;

use App\Form\StatForm;
use EasyCorp\Bundle\EasyAdminBundle\Config\Asset;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;

final class ItemStatField implements FieldInterface
{
    use FieldTrait;

    /**
     * @param TranslatableInterface|string|false|null $label
     */
    public static function new(string $propertyName, $label = null): self
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setTemplateName('crud/field/array')
            ->setFormType(StatForm::class)
            ->addJsFiles(Asset::fromEasyAdminAssetPackage('field-collection.js')->onlyOnForms())
            ->setDefaultColumns('col-md-7 col-xxl-6');
        ;
    }
}