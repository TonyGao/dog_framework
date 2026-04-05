<?php
require __DIR__ . '/vendor/autoload.php';
use App\Kernel;

$kernel = new Kernel('dev', true);
$kernel->boot();
$container = $kernel->getContainer();
$em = $container->get('doctrine')->getManager();
$conn = $em->getConnection();

echo "--- CHECKING USER PREFERENCES IN DB ---\n";
$sql = "SELECT id, user_id, pref_key, pref_value FROM platform_user_preference WHERE pref_key = 'employee_list_columns'";
$stmt = $conn->prepare($sql);
$resultSet = $stmt->executeQuery();
$results = $resultSet->fetchAllAssociative();

if (empty($results)) {
    echo "NO PREFERENCES FOUND IN DATABASE FOR ANY USER.\n";
} else {
    foreach ($results as $row) {
        echo "Row ID: {$row['id']} | User ID: {$row['user_id']}\n";
        echo "Value: " . json_encode(json_decode($row['pref_value']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        echo "---------------------------\n";
    }
}
