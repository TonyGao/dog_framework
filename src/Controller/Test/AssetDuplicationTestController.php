<?php

namespace App\Controller\Test;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AssetDuplicationTestController extends AbstractController
{
    #[Route('/test/asset-duplication', name: 'test_asset_duplication')]
    public function index(): Response
    {
        return $this->render('test/asset_duplication_test.html.twig');
    }
}