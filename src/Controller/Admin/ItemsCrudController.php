<?php

namespace App\Controller\Admin;

use App\Entity\Items;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
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
        $stats = ArrayField::new('stats');
        $image = ImageField::new('image')
            ->setBasePath('/medias/images/balls/')
            ->setUploadDir('public/medias/images/balls/');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $name, $description, $price, $image];
        }

        if (Crud::PAGE_EDIT === $pageName) {
            return [$name, $description, $price, $stats];
        }


        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('Name'),
            TextEditorField::new('Description'),
            IntegerField::new('Price', 'Prix'),
            ImageField::new('Image')
        ];
    }
}
