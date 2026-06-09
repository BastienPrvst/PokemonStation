<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator
    ) {
    }

    public function index(): Response
    {
        $url = $this->adminUrlGenerator
            ->setController(ItemsCrudController::class)
            ->generateUrl();

        return $this->redirect($url);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('App');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::section('Données');
        yield MenuItem::subMenu('Items', 'fas fa-bars')->setSubItems([
            MenuItem::linkTo(ItemsCrudController::class, 'Ajouter un Item', 'fas fa-plus')->setAction(Crud::PAGE_NEW),
            MenuItem::linkTo(ItemsCrudController::class, 'Liste des Items', 'fas fa-list')->setAction(Crud::PAGE_INDEX),
        ]);
        yield MenuItem::subMenu('Utilisateurs', 'fas fa-bars')->setSubItems([
            MenuItem::linkTo(UserCrudController::class, 'Ajouter un utilisateur', 'fas fa-plus')->setAction(Crud::PAGE_NEW),
            MenuItem::linkTo(UserCrudController::class, 'Liste Utilisateurs', 'fas fa-list')->setAction(Crud::PAGE_INDEX),
        ]);
        yield MenuItem::subMenu('News', 'fas fa-bars')->setSubItems([
            MenuItem::linkTo(NewsCrudController::class, 'Ajouter une news', 'fas fa-plus')->setAction(Crud::PAGE_NEW),
            MenuItem::linkTo(NewsCrudController::class, 'Liste des news', 'fas fa-list')->setAction(Crud::PAGE_INDEX),
        ]);
    }
}
