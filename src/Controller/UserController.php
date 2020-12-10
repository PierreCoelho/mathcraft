<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    /**
     * @Route("/tableau-de-bord", name="user_dashboard")
     */
    public function dashboard(Request $request): Response
    {
        return $this->render('user/dashboard.html.twig', [
            'crumbs' => $this->getCrumbs($request),
        ]);
    }
}
