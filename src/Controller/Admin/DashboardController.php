<?php

namespace App\Controller\Admin;

use App\Entity\Items;
use App\Entity\User;
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
            MenuItem::linkToCrud('Ajouter un Item', 'fas fa-plus', Items::class)->setAction(Crud::PAGE_NEW),
            MenuItem::linkToCrud('Liste des Items', 'fas fa-list', Items::class)->setAction(Crud::PAGE_INDEX),
        ]);
        yield MenuItem::subMenu('Utilisateurs', 'fas fa-bars')->setSubItems([
            MenuItem::linkToCrud('Ajouter un utilisateur', 'fas fa-plus', User::class)->setAction(Crud::PAGE_NEW),
            MenuItem::linkToCrud('Liste Utilisateurs', 'fas fa-list', User::class)->setAction(Crud::PAGE_INDEX),
        ]);
    }
}
