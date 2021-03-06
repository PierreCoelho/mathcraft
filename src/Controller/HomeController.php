<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(Request $request): Response
    {
        if($this->getUser()){
            return $this->redirectToRoute('user_dashboard');
        }
        
        return $this->render('home/index.html.twig', [
            'crumbs' => $this->getCrumbs($request),
        ]);
    }
}
