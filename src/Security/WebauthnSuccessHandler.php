<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Routing\RouterInterface;
use App\Entity\Organization\Employee;

class WebauthnSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(private RouterInterface $router)
    {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        $user = $token->getUser();
        $targetUrl = $this->router->generate('employee_list');

        if ($user instanceof Employee) {
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
