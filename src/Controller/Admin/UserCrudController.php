<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $id = IdField::new('id');
        $pseudo = TextField::new('pseudonym');
        $creationDate = DateTimeField::new('creation_date');
        $launchs = NumberField::new('launchs');
        $money  = NumberField::new('money');
        $roles = ArrayField::new('roles');
        $avatar = TextField::new('avatar');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $pseudo, $creationDate, $launchs, $money, $roles, $avatar];
        }

        if (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $pseudo, $creationDate, $launchs, $money, $roles, $avatar];
        }

        if (Crud::PAGE_NEW === $pageName) {
            return [$pseudo, $roles, $avatar, $launchs];
        }

        if (Crud::PAGE_EDIT === $pageName) {
            return [$pseudo, $creationDate, $launchs, $money, $avatar->setRequired(false)];
        }

        return [$id, $pseudo, $creationDate, $launchs, $money, $roles, $avatar];
    }
}
