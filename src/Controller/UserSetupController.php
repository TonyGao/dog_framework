<?php

namespace App\Controller;

use App\Entity\Organization\Employee;
use App\Repository\Organization\EmployeeRepository;
use App\Repository\Security\WebauthnCredentialRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[IsGranted('ROLE_USER')]
class UserSetupController extends AbstractController
{
    #[Route('/user/setup', name: 'app_user_setup')]
    public function setup(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        WebauthnCredentialRepository $credentialRepository,
        EmployeeRepository $employeeRepository,
        TranslatorInterface $translator
    ): Response {
        /** @var Employee $user */
        $user = $this->getUser();

        if (!$user instanceof Employee) {
            return $this->redirectToRoute('employee_list');
        }

        // Step 1: Check Password Reset
        if (!$user->getIsPasswordModifiedByUser()) {
            if ($request->isMethod('POST')) {
                $password = $request->request->get('password');
                $confirmPassword = $request->request->get('confirm_password');

                if ($password !== $confirmPassword) {
                    $this->addFlash('error', $translator->trans('setup.flash.password_mismatch'));
                } elseif (strlen($password) < 8) {
                    $this->addFlash('error', $translator->trans('setup.flash.password_length'));
                } else {
                    $hashedPassword = $passwordHasher->hashPassword($user, $password);
                    $user->setPassword($hashedPassword);
                    $user->setIsPasswordModifiedByUser(true);
                    $entityManager->flush();

                    $this->addFlash('success', $translator->trans('setup.flash.password_updated'));
                    return $this->redirectToRoute('app_user_setup');
                }
            }
            
            return $this->render('user/setup_password.html.twig');
        }

        // Step 2: Check Passkey
        $userEntity = $employeeRepository->findOneByUsername($user->getUserIdentifier());
        
        if ($userEntity) {
            $credentials = $credentialRepository->findAllForUserEntity($userEntity);

            if (count($credentials) === 0) {
                return $this->render('user/setup_passkey.html.twig', [
                    'user' => $user
                ]);
            }
        }
        
        return $this->redirectToRoute('employee_list');
    }
}
