<?php

namespace App\Controller\Admin;

use App\Entity\Items;
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
        yield MenuItem::section('Items');
        yield MenuItem::subMenu('Item', 'fas fa-bars')->setSubItems([
            MenuItem::linkToCrud('Add Item', 'fas fa-plus', Items::class)->setAction(Crud::PAGE_NEW),
            MenuItem::linkToCrud('List Items', 'fas fa-list', Items::class)->setAction(Crud::PAGE_INDEX),
        ]);
    }
}
