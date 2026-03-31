<?php
namespace App\Twig;

use App\Service\Platform\PresenceService;
use Twig\TwigTest;
use Twig\Environment;
use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;
use Symfony\Component\HttpFoundation\RequestStack;

class TwigExtension extends AbstractExtension
{
    private RequestStack $rs;
    private PresenceService $presenceService;

    public function __construct(RequestStack $rs, PresenceService $presenceService)
    {
        $this->rs = $rs;
        $this->presenceService = $presenceService;
    }

    public function getTests(): array {
        return array(
            new TwigTest('instanceof', array($this, 'isInstanceOf')),
         );
     }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('getObjectFields', [$this, 'getObjectFields']),
            new TwigFunction('getStdClassFields', [$this, 'getStdClassFields']),
            new TwigFunction('instanceof', [$this, 'isInstanceof']),
            new TwigFunction('get_session_id', [$this, 'getSessionId']),
            new TwigFunction('generateRandomString', [$this, 'generateRandomString']),
            new TwigFunction('is_online', [$this, 'isOnline']),
            new TwigFunction('mercure_public_url', [$this, 'getMercurePublicUrl']),
        ];
    }

    public function getMercurePublicUrl(): string
    {
        return $this->rs->getCurrentRequest()->server->get('MERCURE_PUBLIC_URL', '');
    }

    /**
     * 判断用户是否在线
     */
    public function isOnline(string $userId): bool
    {
        return $this->presenceService->isOnline($userId);
    }

    public function getObjectFields($object): array
    {
        // 获取对象的所有属性
        $reflection = new \ReflectionClass($object);

        $properties = $reflection->getProperties();
        $fields = [];

        // 获取属性名（字段名）
        foreach ($properties as $property) {
            $fields[] = $property->getName();
        }

        return $fields;
    }

    // public function getStdClassFields($object): array
    // {
    //     // 获取对象的所有属性
    //     $properties = get_object_vars($object);
    //     $fields = [];

    //     // 获取属性名（字段名）
    //     foreach ($properties as $key => $value) {
    //         $fields[] = $key;
    //     }

    //     return $fields;
    // }
    public function getStdClassFields($object): array
    {
        if (!is_object($object)) {
            throw new \InvalidArgumentException('Argument must be an object.');
        }

        // 直接获取对象的属性名称并返回
        return array_keys(get_object_vars($object));
    }

    public function isInstanceof($object, $class)
    {
        if (is_object($object)) {
            $reflectionClass = new \ReflectionClass($class);
            return $reflectionClass->isInstance($object);
        } else {
            return false;
        }
        
    }

    public function getSessionId(): string
    {
        $request = $this->rs->getCurrentRequest();
        if ($request && $request->hasSession()) {
            $session = $request->getSession();
            if (!$session->isStarted()) {
                $session->start();
            }
            $session->set('active', 'active');
            return $session->getId();
        }
        return '';
    }

    public function generateRandomString(int $length = 9): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        
        return $randomString;
    }
}
