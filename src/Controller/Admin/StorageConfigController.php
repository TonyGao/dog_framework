<?php

namespace App\Controller\Admin;

use App\Entity\Storage\StorageConfig;
use App\Form\Storage\StorageConfigType;
use App\Repository\Storage\StorageConfigRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/storage')]
class StorageConfigController extends AbstractController
{
    #[Route('/', name: 'admin_storage_index', methods: ['GET'])]
    public function index(StorageConfigRepository $storageConfigRepository): Response
    {
        $configs = $storageConfigRepository->findAll();
        $tableData = [];

        foreach ($configs as $config) {
            $adapterTypeHtml = match ($config->getAdapterType()) {
                'local' => '<span class="ef-tag ef-tag-blue">本地存储</span>',
                's3' => '<span class="ef-tag ef-tag-orange">S3对象存储</span>',
                default => $config->getAdapterType(),
            };
            
            $isDefaultHtml = $config->isDefault() 
                ? '<span class="ef-tag ef-tag-green">是</span>' 
                : '<span class="ef-tag ef-tag-grey">否</span>';

            $tableData[] = [
                'id' => $config->getId(),
                'name' => $config->getName(),
                'adapterType' => $adapterTypeHtml,
                'isDefault' => $isDefaultHtml,
                'cdnDomain' => $config->getCdnDomain(),
                'createdAt' => $config->getCreatedAt() ? $config->getCreatedAt()->format('Y-m-d H:i:s') : '',
            ];
        }

        $columns = [
            ['field' => 'name', 'label' => '配置名称', 'align' => 'left'],
            ['field' => 'adapterType', 'label' => '存储类型', 'align' => 'center', 'raw' => true],
            ['field' => 'isDefault', 'label' => '默认配置', 'align' => 'center', 'raw' => true],
            ['field' => 'cdnDomain', 'label' => 'CDN域名', 'align' => 'left'],
            ['field' => 'createdAt', 'label' => '创建时间', 'align' => 'center'],
        ];

        return $this->render('admin/storage/index.html.twig', [
            'tableData' => $tableData,
            'columns' => $columns,
        ]);
    }

    #[Route('/drawer', name: 'admin_storage_drawer', methods: ['POST'])]
    public function drawer(Request $request): Response
    {
        $action = $request->getPayload()->get('action');
        $storageConfig = new StorageConfig();
        $form = $this->createForm(StorageConfigType::class, $storageConfig, [
            'action' => $this->generateUrl('admin_storage_new')
        ]);

        return $this->render('admin/storage/create_drawer.html.twig', [
            'form' => $form->createView(),
            'drawerId' => 'storage-config-drawer-new'
        ]);
    }

    #[Route('/new', name: 'admin_storage_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $storageConfig = new StorageConfig();
        $form = $this->createForm(StorageConfigType::class, $storageConfig);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($storageConfig);
            $entityManager->flush();

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['status' => 'success', 'message' => '创建成功']);
            }

            return $this->redirectToRoute('admin_storage_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($request->isXmlHttpRequest() && $form->isSubmitted()) {
             // If validation failed, return the form with errors (rendered as HTML)
             // The client-side logic (ui.formValid) usually replaces the form content with this HTML
             // However, for drawers, we might need to return the drawer content again.
             // But wait, ui.formValid is generic. Let's assume it handles HTML response for errors.
             return $this->render('admin/storage/create_drawer.html.twig', [
                'form' => $form->createView(),
                'drawerId' => 'storage-config-drawer-new'
            ], new Response(null, 422)); // Return 422 for validation errors
        }

        return $this->render('admin/storage/form.html.twig', [
            'storage_config' => $storageConfig,
            'form' => $form->createView(),
            'title' => '新建存储配置'
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_storage_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, StorageConfig $storageConfig, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(StorageConfigType::class, $storageConfig);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('admin_storage_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/storage/form.html.twig', [
            'storage_config' => $storageConfig,
            'form' => $form->createView(),
            'title' => '编辑存储配置'
        ]);
    }

    #[Route('/{id}', name: 'admin_storage_delete', methods: ['POST'])]
    public function delete(Request $request, StorageConfig $storageConfig, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$storageConfig->getId(), $request->request->get('_token'))) {
            $entityManager->remove($storageConfig);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_storage_index', [], Response::HTTP_SEE_OTHER);
    }
}
