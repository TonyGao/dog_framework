<?php
require __DIR__ . '/vendor/autoload.php';

use App\Kernel;

$kernel = new Kernel('dev', true);
$kernel->boot();
$container = $kernel->getContainer();
$em = $container->get('doctrine')->getManager();

$admin = $em->getRepository(\App\Entity\Organization\Employee::class)->findOneBy(['email' => 'admin@example.com']);
if ($admin) {
    $admin->setIsSystem(true);
    $roles = $admin->getRoles();
    if (!in_array('ROLE_SYS_ADMIN', $roles)) {
        $roles[] = 'ROLE_SYS_ADMIN';
    }
    if (!in_array('ROLE_ADMIN', $roles)) {
        $roles[] = 'ROLE_ADMIN';
    }
    // Also clear the `manager` if they shouldn't show in the tree under someone
    $admin->setRoles($roles);
    $em->persist($admin);
    $em->flush();
    echo "Admin user updated: isSystem=true, roles updated.\n";
} else {
    echo "Admin user not found.\n";
}
