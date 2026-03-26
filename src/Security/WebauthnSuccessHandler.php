<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Routing\RouterInterface;
use App\Entity\Organization\Employee;
use App\Repository\Security\WebauthnCredentialRepository;
use Doctrine\ORM\EntityManagerInterface;
use Webauthn\Bundle\Security\Authentication\Token\WebauthnToken;

class WebauthnSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(
        private RouterInterface $router,
        private WebauthnCredentialRepository $credentialRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        if ($token instanceof WebauthnToken) {
            $descriptor = $token->getPublicKeyCredentialDescriptor();
            if ($descriptor) {
                $credential = $this->credentialRepository->findEntityByCredentialId($descriptor->id);
                if ($credential) {
                    $credential->setLastUsedAt(new \DateTimeImmutable());
                    
                    // Update device name on usage
                    $userAgent = $request->headers->get('User-Agent');
                    $credential->setDeviceName($this->credentialRepository->parseUserAgent($userAgent));
                    
                    $this->entityManager->persist($credential);
                    $this->entityManager->flush();
                }
            }
        }

        $user = $token->getUser();
        $targetUrl = $this->router->generate('employee_list');

        // Check if this is a password reset flow
        if ($request->getSession()->has('reset_password_flow')) {
            $request->getSession()->remove('reset_password_flow');
            $targetUrl = $this->router->generate('app_reset_password_new');
        } elseif ($user instanceof Employee) {
            if (!$user->getIsPasswordModifiedByUser()) {
                $targetUrl = $this->router->generate('app_user_setup');
            }
        }

        return new JsonResponse([
            'status' => 'ok',
            'targetUrl' => $targetUrl
        ]);
    }
}
