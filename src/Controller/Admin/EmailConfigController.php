<?php

namespace App\Controller\Admin;

use App\Entity\System\EmailConfig;
use App\Entity\System\EmailTemplate;
use App\Repository\System\EmailConfigRepository;
use App\Repository\System\EmailTemplateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/email')]
class EmailConfigController extends AbstractController
{
    #[Route('/', name: 'admin_email_index')]
    public function index(EmailConfigRepository $configRepository, EmailTemplateRepository $templateRepository): Response
    {
        $configs = $configRepository->findAll();

        $templates = $templateRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('admin/email/index.html.twig', [
            'configs' => $configs,
            'templates' => $templates,
        ]);
    }

    #[Route('/template/editor', name: 'admin_email_template_editor')]
    #[Route('/template/editor/{id}', name: 'admin_email_template_editor_with_id')]
    public function editor(EmailConfigRepository $configRepository, EmailTemplateRepository $templateRepository, ?EmailTemplate $id = null): Response
    {
        $configs = $configRepository->findAll();
        $templates = $templateRepository->findBy([], ['createdAt' => 'DESC']);
        return $this->render('admin/email/editor.html.twig', [
            'configs' => $configs,
            'templates' => $templates,
            'editingTemplate' => $id,
        ]);
    }

    #[Route('/template/test-send', name: 'admin_email_template_test_send', methods: ['POST'])]
    public function testTemplateSend(Request $request, EmailConfigRepository $configRepository, \Twig\Environment $twig): Response
    {
        $content = $request->getContent();
        $data = !empty($content) ? json_decode($content, true) : [];
        if (!is_array($data)) {
            $data = [];
        }

        $subject = $data['subject'] ?? $request->request->get('subject');
        $bodyHtml = $data['bodyHtml'] ?? $request->request->get('bodyHtml');
        $emailConfigId = $data['emailConfigId'] ?? $request->request->get('emailConfigId');
        $testEmail = $data['testEmail'] ?? $request->request->get('testEmail');

        if (!$testEmail) {
            return \App\Controller\Api\ApiResponse::error(json_encode([]), 400, '测试接收邮箱不能为空');
        }

        $config = null;
        if ($emailConfigId) {
            $config = $configRepository->find($emailConfigId);
        }
        if (!$config) {
            $config = $configRepository->findOneBy(['isDefault' => true]);
        }

        if (!$config) {
            return \App\Controller\Api\ApiResponse::error(json_encode([]), 400, '未找到可用的邮件服务器配置');
        }

        try {
            $dsn = sprintf('%s://', $config->getProtocol());
            if ($config->getUsername()) {
                $dsn .= urlencode($config->getUsername());
                if ($config->getPassword()) {
                    $dsn .= ':' . urlencode($config->getPassword());
                }
                $dsn .= '@';
            }
            $dsn .= $config->getHost() . ':' . $config->getPort();

            $transport = \Symfony\Component\Mailer\Transport::fromDsn($dsn);
            $mailer = new \Symfony\Component\Mailer\Mailer($transport);

            // Render twig if it contains variables (dummy data for testing)
            try {
                $renderedSubject = $twig->createTemplate($subject ?: '')->render(['code' => '123456', 'user.email' => 'test@example.com']);
                $renderedBody = $twig->createTemplate($bodyHtml ?: '')->render(['code' => '123456', 'user.email' => 'test@example.com', 'username' => 'john.doe', 'login_url' => 'https://example.com/login', 'reset_url' => 'https://example.com/reset', 'announcement_title' => '系统通知', 'announcement_body' => '系统将于今晚升级']);
            } catch (\Exception $e) {
                // If twig rendering fails (e.g. syntax error in template), fallback to original
                $renderedSubject = $subject;
                $renderedBody = $bodyHtml;
            }

            // Wrap the body with standard email layout only if it doesn't already have it
            $emailLayout = $renderedBody ?: '<p>Empty Body</p>';
            if (strpos($emailLayout, 'id="ef-email-wrapper"') === false) {
                $emailWrapperStart = '<div id="ef-email-wrapper" style="background-color: #f4f5f7; padding: 40px 20px; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif, \'Apple Color Emoji\', \'Segoe UI Emoji\', \'Segoe UI Symbol\'; color: #333333;"><table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" align="center" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); overflow: hidden;"><tr><td style="height: 6px; background: linear-gradient(90deg, #2563eb 0%, #4f46e5 100%);"></td></tr><tr><td style="padding: 40px;">';
                $emailWrapperEnd = '</td></tr><tr><td style="padding: 30px 40px; background-color: #f9f9f9; text-align: center; font-size: 12px; color: #999999; border-top: 1px solid #eeeeee;"><p style="margin: 0 0 10px 0;">© 2025 Your Company Name. All rights reserved.</p><p style="margin: 0;">此邮件为系统自动发送，请勿直接回复。</p></td></tr></table></div>';
                $emailLayout = $emailWrapperStart . $emailLayout . $emailWrapperEnd;
            }

            $email = (new \Symfony\Component\Mime\Email())
                ->from(new \Symfony\Component\Mime\Address($config->getSenderAddress(), $config->getSenderName() ?: ''))
                ->to($testEmail)
                ->subject($renderedSubject ?: 'Test Template Email')
                ->html($emailLayout);

            $mailer->send($email);

            return \App\Controller\Api\ApiResponse::success(json_encode([]), 200, '测试邮件发送成功，已发送至 ' . $testEmail);
        } catch (\Exception $e) {
            return \App\Controller\Api\ApiResponse::error(json_encode([]), 500, '发送失败: ' . $e->getMessage());
        }
    }

    #[Route('/config/save', name: 'admin_email_config_save', methods: ['POST'])]
    public function saveConfig(Request $request, EntityManagerInterface $em, EmailConfigRepository $configRepository): Response
    {
        $id = $request->request->get('id');
        if ($id) {
            $config = $configRepository->find($id);
            if (!$config) {
                throw $this->createNotFoundException('Config not found');
            }
        } else {
            $config = new EmailConfig();
            $em->persist($config);
        }

        $config->setName($request->request->get('name'));
        $isDefault = $request->request->get('isDefault') === '1';
        $config->setIsDefault($isDefault);

        if ($isDefault) {
            // Unset default for all others
            $otherConfigs = $configRepository->findAll();
            foreach ($otherConfigs as $other) {
                if ($other !== $config) {
                    $other->setIsDefault(false);
                }
            }
        } else {
            // If there are no configs, make this one default
            $count = $configRepository->count([]);
            if ($count === 0 || ($count === 1 && $config->getId() !== null)) {
                $config->setIsDefault(true);
            }
        }

        $config->setProtocol($request->request->get('protocol', 'smtp'));
        $config->setHost($request->request->get('host'));
        $config->setPort((int) $request->request->get('port', 465));
        $config->setEncryption($request->request->get('encryption', 'ssl'));
        $config->setUsername($request->request->get('username'));
        
        $password = $request->request->get('password');
        if (!empty($password)) {
            $config->setPassword($password);
        }

        $config->setSenderName($request->request->get('senderName'));
        $config->setSenderAddress($request->request->get('senderAddress'));

        $em->flush();

        $this->addFlash('success', 'Email configuration saved successfully.');

        return $this->redirectToRoute('admin_email_index');
    }

    #[Route('/config/delete/{id}', name: 'admin_email_config_delete', methods: ['POST'])]
    public function deleteConfig(EmailConfig $config, EntityManagerInterface $em): Response
    {
        $em->remove($config);
        $em->flush();

        $this->addFlash('success', 'Email configuration deleted successfully.');

        return $this->redirectToRoute('admin_email_index');
    }

    #[Route('/template/save', name: 'admin_email_template_save', methods: ['POST'])]
    public function saveTemplate(Request $request, EntityManagerInterface $em, EmailTemplateRepository $templateRepository, EmailConfigRepository $configRepository): Response
    {
        $id = $request->request->get('id');
        if ($id) {
            $template = $templateRepository->find($id);
            if (!$template) {
                throw $this->createNotFoundException('Template not found');
            }
        } else {
            $template = new EmailTemplate();
            $em->persist($template);
        }

        $name = $request->request->get('name');
        
        if (empty($name)) {
            $this->addFlash('error', '模版名称不能为空。');
            return $this->redirectToRoute('admin_email_index');
        }

        $existing = $templateRepository->findOneBy(['name' => $name]);
        if ($existing && $existing->getId() != $id) {
            $this->addFlash('error', '模版名称已存在，请使用唯一的名称。');
            return $this->redirectToRoute('admin_email_index');
        }

        $template->setCode($name);
        $template->setName($name);
        $template->setSubject($request->request->get('subject'));
        $template->setBodyHtml($request->request->get('bodyHtml'));
        $template->setDescription($request->request->get('description'));

        $configId = $request->request->get('emailConfigId');
        if ($configId) {
            $emailConfig = $configRepository->find($configId);
            $template->setEmailConfig($emailConfig);
        } else {
            $template->setEmailConfig(null);
        }

        $em->flush();

        $this->addFlash('success', 'Email template saved successfully.');

        return $this->redirectToRoute('admin_email_index');
    }

    #[Route('/template/delete/{id}', name: 'admin_email_template_delete', methods: ['POST'])]
    public function deleteTemplate(EmailTemplate $template, EntityManagerInterface $em): Response
    {
        $em->remove($template);
        $em->flush();

        $this->addFlash('success', 'Email template deleted successfully.');

        return $this->redirectToRoute('admin_email_index');
    }

    #[Route('/template/preview', name: 'admin_email_template_preview', methods: ['POST'])]
    public function previewTemplate(Request $request): Response
    {
        $html = $request->request->get('html');
        $subject = $request->request->get('subject', 'No Subject');
        
        return $this->render('admin/email/preview.html.twig', [
            'html' => $html,
            'subject' => $subject,
        ]);
    }

    #[Route('/config/test-connection', name: 'admin_email_config_test_connection', methods: ['POST'])]
    public function testConnection(Request $request, EmailConfigRepository $configRepository): Response
    {
        $content = $request->getContent();
        $data = !empty($content) ? json_decode($content, true) : [];
        if (!is_array($data)) {
            $data = [];
        }

        $id = $data['id'] ?? $request->request->get('id');
        $protocol = $data['protocol'] ?? $request->request->get('protocol', 'smtp');
        $host = $data['host'] ?? $request->request->get('host');
        $port = (int) ($data['port'] ?? $request->request->get('port', 465));
        $username = $data['username'] ?? $request->request->get('username');
        $password = $data['password'] ?? $request->request->get('password');
        $senderAddress = $data['senderAddress'] ?? $request->request->get('senderAddress');
        $senderName = $data['senderName'] ?? $request->request->get('senderName');
        $testEmail = $data['testEmail'] ?? $request->request->get('testEmail') ?: $senderAddress;

        if (!$password && $id) {
            $config = $configRepository->find($id);
            if ($config) {
                $password = $config->getPassword();
            }
        }

        try {
            $dsn = sprintf('%s://', $protocol);
            if ($username) {
                $dsn .= urlencode($username);
                if ($password) {
                    $dsn .= ':' . urlencode($password);
                }
                $dsn .= '@';
            }
            $dsn .= $host . ':' . $port;

            $transport = \Symfony\Component\Mailer\Transport::fromDsn($dsn);
            $mailer = new \Symfony\Component\Mailer\Mailer($transport);

            $email = (new \Symfony\Component\Mime\Email())
                ->from(new \Symfony\Component\Mime\Address($senderAddress, $senderName ?: ''))
                ->to($testEmail) // Send test email to the provided test email
                ->subject('Test Connection from Enterprise Framework')
                ->text('这是一封用于验证邮件服务器配置的测试邮件。This is a test email to verify the server configuration.');

            $mailer->send($email);

            return \App\Controller\Api\ApiResponse::success(json_encode([]), 200, '连接成功，测试邮件已发送至 ' . $testEmail);
        } catch (\Exception $e) {
            return \App\Controller\Api\ApiResponse::error(json_encode([]), 500, '连接失败: ' . $e->getMessage());
        }
    }
}
