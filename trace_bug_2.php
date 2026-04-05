<?php
require __DIR__ . '/vendor/autoload.php';

use App\Kernel;

$kernel = new Kernel('dev', true);
$kernel->boot();
$container = $kernel->getContainer();
$em = $container->get('doctrine')->getManager();

// 1. Get the admin user just like in the controller
$user = $em->getRepository(\App\Entity\Organization\Employee::class)->findOneBy(['email' => 'admin@example.com']);
if (!$user) {
    die("Admin user not found\n");
}

echo "=== TRACING THE EXACT CODE IN EmployeeController ===\n";

$prefRepo = $em->getRepository(\App\Entity\Platform\UserPreference::class);

echo "1. User exists.\n";
$userId = method_exists($user, 'getId') ? (string)$user->getId() : $user->getUserIdentifier();
echo "2. Generated \$userId: '{$userId}'\n";

$userPref = $prefRepo->findOneBy(['userId' => $userId, 'prefKey' => 'employee_list_columns']);

if ($userPref) {
    echo "SUCCESS: Found preference record in database!\n";
    $value = $userPref->getPrefValue();
    echo "4. Raw Type of \$userPref->getPrefValue(): " . gettype($value) . "\n";
    echo "Raw Content: " . print_r($value, true) . "\n";
    
    // Simulate what the controller *actually* does right now
    $columns = [];
    if (is_array($value) && isset($value['columns'])) {
         echo "--> Branch 1: It is an array and has 'columns' key.\n";
    } elseif (is_string($value)) {
         echo "--> Branch 2: It is a string. Trying to decode...\n";
         $decoded = json_decode($value, true);
         echo "Decoded Type: " . gettype($decoded) . "\n";
         if (is_array($decoded) && isset($decoded['columns'])) {
             echo "--> SUCCESS: Decoded array has 'columns' key!\n";
         } else {
             echo "--> FAIL: Decoded array DOES NOT have 'columns' key!\n";
             print_r($decoded);
         }
    } else {
         echo "--> Branch 3: Unknown state.\n";
    }
} else {
    echo "FAIL: Could not find any preference record for this userId.\n";
    
    // Fallback test
    echo "Let's see if there's ANY record for this key...\n";
    $anyPref = $prefRepo->findBy(['prefKey' => 'employee_list_columns']);
    foreach ($anyPref as $p) {
        echo "- Found record with userId: '{$p->getUserId()}'\n";
    }
}
