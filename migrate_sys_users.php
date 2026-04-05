<?php
require __DIR__ . '/vendor/autoload.php';

use App\Kernel;
use App\Entity\Organization\Employee;
use Symfony\Component\Dotenv\Dotenv;

(new Dotenv())->bootEnv(__DIR__.'/.env');

$kernel = new Kernel('dev', true);
$kernel->boot();
$container = $kernel->getContainer();
$em = $container->get('doctrine')->getManager();
$conn = $em->getConnection();

$sysUsers = $conn->fetchAllAssociative('SELECT * FROM sys_user');

foreach ($sysUsers as $sysUser) {
    $existing = $em->getRepository(Employee::class)->findOneBy(['username' => $sysUser['username']]);
    if (!$existing) {
        $emp = new Employee();
        // Since we force ID on creation, we can't easily set UUID, but doctrine will auto generate one.
        $emp->setUsername($sysUser['username']);
        $emp->setEmail($sysUser['username'] . '@system.local');
        $emp->setPassword($sysUser['password']);
        $emp->setRoles(json_decode($sysUser['roles'], true));
        $emp->setName($sysUser['username']);
        $emp->setEmployeeNo('SYS_' . strtoupper($sysUser['username']));
        $emp->setEmploymentStatus('active');
        $emp->setWorkStatus('working');
        $emp->setIsSystem(true);
        
        $em->persist($emp);
        echo "Migrated {$sysUser['username']} to Employee.\n";
    } else {
        $existing->setIsSystem(true);
        $roles = array_unique(array_merge($existing->getRoles(), json_decode($sysUser['roles'], true)));
        $existing->setRoles($roles);
        $em->persist($existing);
        echo "Updated existing employee {$sysUser['username']} to be a system user.\n";
    }
}
$em->flush();
echo "Migration complete.\n";
