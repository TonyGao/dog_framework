<?php
namespace App\Twig;

use Twig\TwigTest;
use Twig\Environment;
use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\SessionFactoryInterface;

class TwigExtension extends AbstractExtension
{
    private $session;

    public function __construct(RequestStack $rs)
    {
        if (php_sapi_name() === 'cli') {
            // 在 CLI 环境下不执行会话相关代码
            return;
        }
        
        $this->session = $rs->getSession();
        $this->session->set('active', 'active');
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
        ];
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
        /** @var SessionInterface $session */
        return $this->session->getId();
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
