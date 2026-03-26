<?php

namespace App\Controller\Admin;

use App\Entity\Security\PasswordResetMethod;
use App\Repository\Security\PasswordResetMethodRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/security')]
class SecurityConfigController extends AbstractController
{
    #[Route('/', name: 'admin_security_index')]
    public function index(PasswordResetMethodRepository $repository, \App\Repository\System\EmailTemplateRepository $templateRepository): Response
    {
        // Ensure default methods exist
        if ($repository->count([]) === 0) {
            return $this->redirectToRoute('admin_security_init');
        }

        $methods = $repository->findBy([], ['priority' => 'ASC']);
        $emailTemplates = $templateRepository->findAll();

        return $this->render('admin/security/index.html.twig', [
            'methods' => $methods,
            'emailTemplates' => $emailTemplates,
        ]);
    }

    #[Route('/init', name: 'admin_security_init')]
    public function init(EntityManagerInterface $em, PasswordResetMethodRepository $repository): Response
    {
        $defaults = [
            ['key' => 'passkey', 'name' => 'Passkey', 'priority' => 10],
            ['key' => 'email', 'name' => 'Email', 'priority' => 20],
            ['key' => 'sms', 'name' => 'SMS', 'priority' => 30],
            ['key' => 'qa', 'name' => 'Security Questions', 'priority' => 40],
        ];

        foreach ($defaults as $default) {
            $method = $repository->findOneBy(['methodKey' => $default['key']]);
            if (!$method) {
                $method = new PasswordResetMethod();
                $method->setMethodKey($default['key']);
                $method->setName($default['name']);
                $method->setPriority($default['priority']);
                $method->setIsEnabled(true);
                $em->persist($method);
            }
        }

        $em->flush();
        $this->addFlash('success', 'Security methods initialized.');

        return $this->redirectToRoute('admin_security_index');
    }

    #[Route('/update/{id}', name: 'admin_security_update', methods: ['POST'])]
    public function update(Request $request, PasswordResetMethod $method, EntityManagerInterface $em): Response
    {
        $priority = $request->request->get('priority');
        $isEnabled = $request->request->has('is_enabled');
        $config = $request->request->all('config');

        if ($priority !== null) {
            $method->setPriority((int) $priority);
        }
        
        $method->setIsEnabled($isEnabled);
        
        if (!empty($config)) {
            $method->setConfig($config);
        }

        $em->flush();

        $this->addFlash('success', 'Method updated.');

        return $this->redirectToRoute('admin_security_index');
    }

    #[Route('/reorder', name: 'admin_security_reorder', methods: ['POST'])]
    public function reorder(Request $request, PasswordResetMethodRepository $repository, EntityManagerInterface $em): Response
    {
        $data = json_decode($request->getContent(), true);
        $ids = $data['ids'] ?? [];

        if (empty($ids)) {
            return $this->json(['status' => 'error', 'message' => 'No IDs provided'], 400);
        }

        foreach ($ids as $index => $id) {
            $method = $repository->find($id);
            if ($method) {
                // Priority: 10, 20, 30...
                $method->setPriority(($index + 1) * 10);
            }
        }

        $em->flush();

        return $this->json(['status' => 'success']);
    }
}
