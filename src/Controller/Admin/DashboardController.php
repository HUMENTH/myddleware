<?php

namespace App\Controller\Admin;

use App\Entity\Job;
use App\Entity\Rule;
use App\Entity\User;
use App\Entity\Module;
use App\Entity\Solution;
use App\Entity\Connector;
use App\Entity\JobScheduler;
use App\Entity\ConnectorParam;
use Symfony\UX\Chartjs\Model\Chart;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

class DashboardController extends AbstractDashboardController
{
    public function __construct(ChartBuilderInterface $chartBuilder){
        $this->chartBuilder = $chartBuilder;
    }

    private function createChart(): Chart
    {
        $chart = $this->chartBuilder->createChart(Chart::TYPE_LINE);
        $chart->setData([
            'labels' => ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
            'datasets' => [
                [
                    'label' => 'My First dataset',
                    'backgroundColor' => 'rgb(255, 99, 132)',
                    'borderColor' => 'rgb(255, 99, 132)',
                    'data' => [0, 10, 5, 2, 20, 30, 45],
                ],
            ],
        ]);
        $chart->setOptions([
            'scales' => [
                'y' => [
                   'suggestedMin' => 0,
                   'suggestedMax' => 100,
                ],
            ],
        ]);
        return $chart;
    }

    #[Route('/admin', name: 'admin_dashboard')]
    public function index(): Response
    {
        //assert(null !== $chartBuilder);
        // $chart = $chartBuilder->createChart(Chart::TYPE_LINE);

        // $chart->setData([
        //     'labels' => ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
        //     'datasets' => [
        //         [
        //             'label' => 'My First dataset',
        //             'backgroundColor' => 'rgb(255, 99, 132)',
        //             'borderColor' => 'rgb(255, 99, 132)',
        //             'data' => [0, 10, 5, 2, 20, 30, 45],
        //         ],
        //     ],
        // ]);
        
        // $chart->setOptions([
        //     'scales' => [
        //         'y' => [
        //             'suggestedMin' => 0,
        //             'suggestedMax' => 100,
        //         ],
        //     ],
        // ]);

        
        // Option 1. You can make your dashboard redirect to some common page of your backend
        // $routeBuilder = $this->container->get(AdminUrlGenerator::class);
        // $url = $routeBuilder->setController(ConnectorCrudController::class)->generateUrl();
        // return $this->redirect($url);
   
        return $this->render('admin/my-dashboard.html.twig', [
            //'chart' => $chart,
            'chart' => $this->createChart(),
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('<img src="build/images/logo/logo.png" alt="Myddleware">')
            ->renderContentMaximized();
    }

    public function configureMenuItems(): iterable
    {
        return [
            MenuItem::linkToDashboard('Dashboard', 'fa fa-home'),
            MenuItem::section('Rules'),
            MenuItem::subMenu('Rules', 'fas fa-sync')->setSubItems([
                MenuItem::linkToCrud('My rules', 'fas fa-eye', Rule::class),
                MenuItem::linkToCrud('Create new rule', 'fas fa-plus', Rule::class)->setAction(Crud::PAGE_NEW),
            ]),
            MenuItem::section('Connectors'),
            MenuItem::subMenu('Connectors', 'fa fa-link')->setSubItems([
                MenuItem::linkToCrud('My connectors', 'fa fa-eye', Connector::class),
                MenuItem::linkToCrud('Add connector', 'fa fa-plus', Connector::class)->setAction(Crud::PAGE_NEW),
                MenuItem::linkToCrud('Credentials', 'fa fa-plug', ConnectorParam::class),
                MenuItem::linkToCrud('Add credentials', 'fas fa-plus', ConnectorParam::class)->setAction(Crud::PAGE_NEW),
            ]),
            MenuItem::section('Solutions'),
            MenuItem::linkToCrud('Solutions', 'fa fa-bullseye', Solution::class),
            MenuItem::linkToCrud('Modules', 'fa fa-cubes', Module::class),
            MenuItem::section('Jobs'),
            MenuItem::linkToCrud('Job', 'fas fa-tasks', Job::class),
            MenuItem::linkToCrud('Job Scheduler', 'fa fa-calendar', JobScheduler::class),
            MenuItem::section('Users'),
            MenuItem::linkToCrud('User', 'fa fa-user', User::class),
        ];
    }
}
