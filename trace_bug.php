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

// --- Simulate Controller Code ---
$prefRepo = $em->getRepository(\App\Entity\Platform\UserPreference::class);
$userPref = null;

echo "1. Checking if user exists...\n";
if ($user) {
    echo "User exists.\n";
    echo "Is method 'getId' callable? " . (method_exists($user, 'getId') ? 'Yes' : 'No') . "\n";
    
    // Simulate the line causing trouble
    $userId = method_exists($user, 'getId') ? (string)$user->getId() : $user->getUserIdentifier();
    echo "2. Generated \$userId: '{$userId}'\n";
    
    echo "3. Querying UserPreference with ['userId' => '{$userId}', 'prefKey' => 'employee_list_columns']\n";
    $userPref = $prefRepo->findOneBy(['userId' => $userId, 'prefKey' => 'employee_list_columns']);
    
    if ($userPref) {
        echo "SUCCESS: Found preference record in database!\n";
        
        $value = $userPref->getPrefValue();
        echo "4. Type of \$userPref->getPrefValue(): " . gettype($value) . "\n";
        
        if (is_array($value)) {
            echo "Value is an array.\n";
            if (isset($value['columns'])) {
                echo "SUCCESS: 'columns' key exists in array!\n";
                echo "Here are the columns to render:\n";
                print_r(array_keys($value['columns']));
            } else {
                echo "FAIL: 'columns' key DOES NOT exist in the array.\n";
                print_r($value);
            }
        } elseif (is_string($value)) {
            echo "Value is a string. Content: \n$value\n";
            echo "FAIL: The code expects an array but got a string. This is why it skips the custom columns!\n";
            
            // Let's see if json_decode fixes it
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                echo "When json_decoded, it becomes an array!\n";
                if (isset($decoded['columns'])) {
                     echo "And 'columns' key exists!\n";
                }
            }
        } else {
            echo "Value is something else: " . var_export($value, true) . "\n";
        }
    } else {
        echo "FAIL: Could not find any preference record for this userId.\n";
    }
}
