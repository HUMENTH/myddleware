<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LogoutController extends AbstractController
{
    /**
     * This is automatically detected by Symfony: when the user attempts to reach it, 
     * they are automatically logged out and redirected to '/'
     *
     * @return void
     */
    #[Route('/logout', name: 'logout')]
    public function logout()
    {
    }
}