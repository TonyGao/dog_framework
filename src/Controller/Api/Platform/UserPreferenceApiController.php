<?php

namespace App\Controller\Api\Platform;

use App\Controller\Api\ApiResponse;
use App\Entity\Platform\UserPreference;
use App\Entity\Organization\Employee;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserPreferenceApiController extends AbstractController
{
    #[Route('/api/user/preference/save', name: 'api_user_preference_save', methods: ['POST'])]
    public function save(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return ApiResponse::error(json_encode(['message' => 'User not logged in']), 401, 'Unauthorized');
        }

        $data = json_decode($request->getContent(), true);
        $key = $data['key'] ?? null;
        $value = $data['value'] ?? null;

        if (!$key || !$value) {
            return ApiResponse::error(json_encode(['message' => 'Invalid data']), 400, 'Bad Request');
        }

        $userId = $user->getUserIdentifier();
        if ($user instanceof \App\Entity\Organization\Employee) {
            $userId = (string)$user->getId();
        } else {
            $userId = (string)$userId;
        }

        $repo = $em->getRepository(UserPreference::class);
        $pref = $repo->findOneBy(['userId' => $userId, 'prefKey' => $key]);

        if (!$pref) {
            $pref = new UserPreference();
            $pref->setUserId($userId);
            $pref->setPrefKey($key);
        }

        $pref->setPrefValue($value);
        $em->persist($pref);
        $em->flush();

        return ApiResponse::success(json_encode(['message' => 'Preference saved']), 200, 'Success');
    }

    #[Route('/api/user/preference/get', name: 'api_user_preference_get', methods: ['GET'])]
    public function get(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return ApiResponse::error(json_encode(['message' => 'User not logged in']), 401, 'Unauthorized');
        }

        $key = $request->query->get('key');
        if (!$key) {
            return ApiResponse::error(json_encode(['message' => 'Key is required']), 400, 'Bad Request');
        }

        $userId = $user->getUserIdentifier();
        if ($user instanceof \App\Entity\Organization\Employee) {
            $userId = (string)$user->getId();
        } else {
            $userId = (string)$userId;
        }

        $repo = $em->getRepository(UserPreference::class);
        $pref = $repo->findOneBy(['userId' => $userId, 'prefKey' => $key]);

        return ApiResponse::success(json_encode(['value' => $pref ? $pref->getPrefValue() : null]), 200, 'Success');
    }
}
