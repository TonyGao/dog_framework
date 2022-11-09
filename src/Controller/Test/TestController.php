<?php

namespace App\Controller\Test;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{
  /**
   * @Route("/", methods="GET", name="index_page")
   */
  public function index(Request $request): Response
  {
    return $this->render('test/button.html.twig');
  }

}