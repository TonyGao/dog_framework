<?php

namespace App\Service;

use App\Entity\System\EmailLog;
use App\Repository\System\EmailConfigRepository;
use App\Repository\System\EmailTemplateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class MailService
{
    private EmailConfigRepository $configRepository;
    private EmailTemplateRepository $templateRepository;
    private Environment $twig;
    private EntityManagerInterface $em;

    public function __construct(
        EmailConfigRepository $configRepository,
        EmailTemplateRepository $templateRepository,
        Environment $twig,
        EntityManagerInterface $em
    ) {
        $this->configRepository = $configRepository;
        $this->templateRepository = $templateRepository;
        $this->twig = $twig;
        $this->em = $em;
    }

    public function send(string $to, string $templateCode, array $context = []): void
    {
        $log = new EmailLog();
        $log->setRecipient($to);
        $log->setTemplateCode($templateCode);
        $log->setStatus('pending');

        try {
            $template = $this->templateRepository->findOneBy(['code' => $templateCode]);
            if (!$template) {
                throw new \RuntimeException(sprintf('Email template "%s" not found.', $templateCode));
            }

            $config = $template->getEmailConfig();
            if (!$config) {
                $config = $this->configRepository->findOneBy(['isDefault' => true]);
                if (!$config) {
                    $configs = $this->configRepository->findAll();
                    if (count($configs) === 0) {
                        throw new \RuntimeException('No email configuration is set.');
                    }
                    $config = $configs[0];
                }
            }

            // Render subject and body using Twig
            $twigTemplateSubject = $this->twig->createTemplate($template->getSubject());
            $subject = $twigTemplateSubject->render($context);
            $log->setSubject($subject);

            $twigTemplateBody = $this->twig->createTemplate($template->getBodyHtml());
            $bodyHtml = $twigTemplateBody->render($context);

            // Build DSN
            $protocol = $config->getProtocol() ?: 'smtp';
            $host = $config->getHost();
            $port = $config->getPort() ?: 25;
            $username = $config->getUsername();
            $password = $config->getPassword();
            
            $dsn = sprintf('%s://', $protocol);
            if ($username) {
                $dsn .= urlencode($username);
                if ($password) {
                    $dsn .= ':' . urlencode($password);
                }
                $dsn .= '@';
            }
            $dsn .= $host . ':' . $port;

            $transport = Transport::fromDsn($dsn);
            $mailer = new Mailer($transport);

            $email = (new Email())
                ->from(new \Symfony\Component\Mime\Address($config->getSenderAddress(), $config->getSenderName() ?: ''))
                ->to($to)
                ->subject($subject)
                ->html($bodyHtml);

            $mailer->send($email);

            $log->setStatus('success');
            $log->setSentAt(new \DateTime());
        } catch (\Exception $e) {
            $log->setStatus('failed');
            $log->setErrorMessage($e->getMessage());
            throw $e;
        } finally {
            $this->em->persist($log);
            $this->em->flush();
        }
    }
}
