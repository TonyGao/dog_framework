<?php

namespace App\Service;

use App\Entity\System\EmailLog;
use App\Repository\System\EmailConfigRepository;
use App\Repository\System\EmailTemplateRepository;
use App\Repository\System\EmailFunctionBindingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class MailService
{
    private EmailConfigRepository $configRepository;
    private EmailTemplateRepository $templateRepository;
    private EmailFunctionBindingRepository $bindingRepository;
    private Environment $twig;
    private EntityManagerInterface $em;

    public function __construct(
        EmailConfigRepository $configRepository,
        EmailTemplateRepository $templateRepository,
        EmailFunctionBindingRepository $bindingRepository,
        Environment $twig,
        EntityManagerInterface $em
    ) {
        $this->configRepository = $configRepository;
        $this->templateRepository = $templateRepository;
        $this->bindingRepository = $bindingRepository;
        $this->twig = $twig;
        $this->em = $em;
    }

    /**
     * Sends an email based on the configured functional binding.
     * Throws \DomainException if the function is not bound to a template.
     */
    public function sendForFunction(string $to, string $functionCode, array $context = []): void
    {
        $binding = $this->bindingRepository->findOneBy(['functionCode' => $functionCode]);
        if (!$binding) {
            throw new \DomainException(sprintf('Email function "%s" is not initialized in the database.', $functionCode));
        }

        $template = $binding->getEmailTemplate();
        if (!$template) {
            throw new \DomainException(sprintf('Email function "%s" has no template bound to it.', $functionCode));
        }

        $config = $binding->getEmailConfig();
        
        $this->executeSend($to, $template, $config, $context);
    }

    public function send(string $to, string $templateCode, array $context = []): void
    {
        $template = $this->templateRepository->findOneBy(['code' => $templateCode]);
        if (!$template) {
            throw new \RuntimeException(sprintf('Email template "%s" not found.', $templateCode));
        }
        $config = $template->getEmailConfig();
        $this->executeSend($to, $template, $config, $context);
    }

    private function executeSend(string $to, \App\Entity\System\EmailTemplate $template, ?\App\Entity\System\EmailConfig $config, array $context = []): void
    {
        $log = new EmailLog();
        $log->setRecipient($to);
        $log->setTemplateCode($template->getCode() ?: 'custom');
        $log->setStatus('pending');

        try {
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
