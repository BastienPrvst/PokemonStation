<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Field\ItemStatField;
use App\Entity\Items;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ItemsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Items::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $id = IdField::new('id');
        $name = TextField::new('name');
        $description = TextareaField::new('description');
        $price = NumberField::new('price');
        $stats = ItemStatField::new('stats');
        $image = ImageField::new('image')
            ->setBasePath('/medias/images/balls/')
            ->setUploadDir('public/medias/images/balls/')
            ->setRequired(false);

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $name, $description, $price, $image];
        }

        if (Crud::PAGE_EDIT === $pageName) {
            return [$name, $description, $price, $stats];
        }

        if (Crud::PAGE_NEW === $pageName) {
            return [$name, $description, $price, $stats, $image->setRequired(true)];
        }


        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('Name'),
            TextEditorField::new('Description'),
            IntegerField::new('Price', 'Prix'),
            ImageField::new('Image')
                ->setBasePath('/medias/images/balls/')
                ->setUploadDir('public/medias/images/balls/'),
        ];
    }
}
