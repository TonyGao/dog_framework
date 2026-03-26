<?php

namespace App\Controller\Security;

use App\Repository\Organization\EmployeeRepository;
use App\Repository\Security\PasswordResetMethodRepository;
use App\Repository\Security\WebauthnCredentialRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\Organization\Employee;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/reset-password')]
class ResetPasswordController extends AbstractController
{
    #[Route('/', name: 'app_reset_password')]
    public function index(
        Request $request, 
        EmployeeRepository $employeeRepository,
        PasswordResetMethodRepository $methodRepository,
        WebauthnCredentialRepository $credentialRepository
    ): Response
    {
        $username = $request->query->get('username');
        $availableMethods = [];
        $user = null;

        if ($username) {
            // Try to find user by username, email, or mobile
            $user = $employeeRepository->findOneBy(['username' => $username]);
            if (!$user) {
                $user = $employeeRepository->findOneBy(['email' => $username]);
            }
            if (!$user) {
                 $user = $employeeRepository->findOneBy(['mobile' => $username]);
            }
            if (!$user) {
                 $user = $employeeRepository->findOneBy(['employeeNo' => $username]);
            }

            if ($user) {
                $methods = $methodRepository->findEnabledMethodsOrderedByPriority();
                foreach ($methods as $method) {
                    $key = $method->getMethodKey();
                    $isAvailableForUser = false;
                    switch ($key) {
                        case 'passkey':
                            $credentials = $credentialRepository->findBy(['employee' => $user]);
                            if (count($credentials) > 0) $isAvailableForUser = true;
                            break;
                        case 'email':
                            if ($user->getEmail()) $isAvailableForUser = true;
                            break;
                        case 'sms':
                            if (method_exists($user, 'getMobile') && $user->getMobile()) $isAvailableForUser = true;
                            break;
                        case 'qa':
                            if ($user->getSecurityAnswers() && count($user->getSecurityAnswers()) > 0) $isAvailableForUser = true;
                            break;
                    }
                    if ($isAvailableForUser) {
                        $availableMethods[] = $method;
                    }
                }
            } else {
                $this->addFlash('error', '未找到该用户。');
            }
        }

        return $this->render('security/reset_password/index.html.twig', [
            'username' => $username,
            'user' => $user,
            'availableMethods' => $availableMethods
        ]);
    }

    #[Route('/check', name: 'app_reset_password_check', methods: ['POST'])]
    public function check(
        Request $request,
        EmployeeRepository $employeeRepository,
        PasswordResetMethodRepository $methodRepository,
        WebauthnCredentialRepository $credentialRepository
    ): Response {
        $identifier = $request->request->get('identifier');
        return $this->redirectToRoute('app_reset_password', ['username' => $identifier]);
    }

    #[Route('/verify/passkey/{id}', name: 'app_reset_password_passkey')]
    public function verifyPasskey($id, Request $request, EmployeeRepository $employeeRepository): Response
    {
        $user = $employeeRepository->find($id);
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        // Set session flag so WebauthnSuccessHandler knows to redirect to password reset page
        $request->getSession()->set('reset_password_flow', true);
        
        return $this->render('security/reset_password/passkey.html.twig', [
            'id' => $id,
            'user' => $user
        ]);
    }

    #[Route('/verify/email/{id}', name: 'app_reset_password_email')]
    public function verifyEmail($id, Request $request, EmployeeRepository $employeeRepository): Response
    {
        $user = $employeeRepository->find($id);
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        // Generate and send code (Mock) if not present or resend requested
        if ($request->query->get('resend') || !$request->getSession()->has('reset_password_code_' . $id)) {
            $code = (string) random_int(100000, 999999);
            $request->getSession()->set('reset_password_code_' . $id, $code);
            // In a real app, send email here
            $this->addFlash('info', 'Verification code sent to ' . $user->getEmail() . ' (Mock: ' . $code . ')');
        }

        return $this->render('security/reset_password/email.html.twig', [
            'id' => $id,
            'user' => $user
        ]);
    }

    #[Route('/verify/email/check/{id}', name: 'app_reset_password_email_check', methods: ['POST'])]
    public function verifyEmailCheck($id, Request $request, EmployeeRepository $employeeRepository): Response
    {
        $user = $employeeRepository->find($id);
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $submittedCode = $request->request->get('code');
        $storedCode = $request->getSession()->get('reset_password_code_' . $id);

        if ($submittedCode === $storedCode) {
            // Code verified
            $request->getSession()->remove('reset_password_code_' . $id);
            $request->getSession()->set('reset_password_verified_user_id', $user->getId());
            return $this->redirectToRoute('app_reset_password_new');
        }

        $this->addFlash('error', 'Invalid verification code.');
        return $this->redirectToRoute('app_reset_password_email', ['id' => $id]);
    }

    #[Route('/verify/sms/{id}', name: 'app_reset_password_sms')]
    public function verifySms($id, Request $request, EmployeeRepository $employeeRepository): Response
    {
        $user = $employeeRepository->find($id);
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        // Generate and send code (Mock) if not present or resend requested
        if ($request->query->get('resend') || !$request->getSession()->has('reset_password_sms_code_' . $id)) {
            $code = (string) random_int(100000, 999999);
            $request->getSession()->set('reset_password_sms_code_' . $id, $code);
            // In a real app, send SMS here
            $this->addFlash('info', 'SMS Verification code sent to ' . $user->getMobile() . ' (Mock: ' . $code . ')');
        }

        return $this->render('security/reset_password/sms.html.twig', [
            'id' => $id,
            'user' => $user
        ]);
    }

    #[Route('/verify/sms/check/{id}', name: 'app_reset_password_sms_check', methods: ['POST'])]
    public function verifySmsCheck($id, Request $request, EmployeeRepository $employeeRepository): Response
    {
        $user = $employeeRepository->find($id);
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $submittedCode = $request->request->get('code');
        $storedCode = $request->getSession()->get('reset_password_sms_code_' . $id);

        if ($submittedCode === $storedCode) {
            // Code verified
            $request->getSession()->remove('reset_password_sms_code_' . $id);
            $request->getSession()->set('reset_password_verified_user_id', $user->getId());
            return $this->redirectToRoute('app_reset_password_new');
        }

        $this->addFlash('error', 'Invalid verification code.');
        return $this->redirectToRoute('app_reset_password_sms', ['id' => $id]);
    }

    #[Route('/verify/qa/{id}', name: 'app_reset_password_qa')]
    public function verifyQa($id, Request $request, EmployeeRepository $employeeRepository): Response
    {
        $user = $employeeRepository->find($id);
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        return $this->render('security/reset_password/qa.html.twig', [
            'id' => $id,
            'user' => $user
        ]);
    }

    #[Route('/verify/qa/check/{id}', name: 'app_reset_password_qa_check', methods: ['POST'])]
    public function verifyQaCheck($id, Request $request, EmployeeRepository $employeeRepository): Response
    {
        $user = $employeeRepository->find($id);
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $answer1 = $request->request->get('answer1');
        $securityAnswers = $user->getSecurityAnswers();

        // Check against the first answer (assuming simple 1 question for now, or match any)
        // In real app, we might ask specific question.
        // For now, let's assume securityAnswers is ['q1' => 'a1'] and we check if any value matches.
        
        $verified = false;
        if ($securityAnswers && is_array($securityAnswers)) {
            foreach ($securityAnswers as $storedAnswer) {
                if (trim(strtolower($storedAnswer)) === trim(strtolower($answer1))) {
                    $verified = true;
                    break;
                }
            }
        }

        if ($verified) {
             $request->getSession()->set('reset_password_verified_user_id', $user->getId());
             return $this->redirectToRoute('app_reset_password_new');
        }

        $this->addFlash('error', 'Invalid answer.');
        return $this->redirectToRoute('app_reset_password_qa', ['id' => $id]);
    }

    #[Route('/new', name: 'app_reset_password_new')]
    public function newPassword(Request $request, EmployeeRepository $employeeRepository): Response
    {
        // Check if user is logged in (Passkey flow)
        $user = $this->getUser();

        // Check if user is verified via session (Email/SMS flow)
        if (!$user && $request->getSession()->has('reset_password_verified_user_id')) {
            $userId = $request->getSession()->get('reset_password_verified_user_id');
            $user = $employeeRepository->find($userId);
        }

        if (!$user) {
            return $this->redirectToRoute('app_reset_password');
        }

        return $this->render('security/reset_password/new.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/update', name: 'app_reset_password_update', methods: ['POST'])]
    public function updatePassword(
        Request $request, 
        EmployeeRepository $employeeRepository, 
        UserPasswordHasherInterface $passwordHasher,
        \Doctrine\ORM\EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage
    ): Response
    {
        // Check if user is logged in (Passkey flow)
        $user = $this->getUser();

        // Check if user is verified via session (Email/SMS flow)
        if (!$user && $request->getSession()->has('reset_password_verified_user_id')) {
            $userId = $request->getSession()->get('reset_password_verified_user_id');
            $user = $employeeRepository->find($userId);
        }

        if (!$user || !($user instanceof Employee)) {
             return $this->redirectToRoute('app_reset_password');
        }

        $plainPassword = $request->request->get('password');
        $confirmPassword = $request->request->get('confirm_password');

        if (empty($plainPassword) || $plainPassword !== $confirmPassword) {
            $this->addFlash('error', 'Passwords do not match or are empty.');
            return $this->redirectToRoute('app_reset_password_new');
        }
        
        if (strlen($plainPassword) < 8) {
            $this->addFlash('error', 'Password must be at least 8 characters long.');
            return $this->redirectToRoute('app_reset_password_new');
        }

        // Hash password
        $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);
        $user->setIsPasswordModifiedByUser(true); // Mark as self-modified

        $entityManager->persist($user);
        $entityManager->flush();

        // Clear session flags
        $request->getSession()->remove('reset_password_verified_user_id');
        
        // Force logout to ensure they login with new password
        $tokenStorage->setToken(null);
        // We don't invalidate session here to keep flash messages, but remove security context
        $request->getSession()->remove('_security_main'); 
        
        $this->addFlash('success', 'Password updated successfully. Please login with your new password.');

        return $this->redirectToRoute('app_login');
    }
}
