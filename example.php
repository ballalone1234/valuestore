<?php

/**
 * Example usage of Valuestore with PHP 8.2+ features
 * 
 * This file demonstrates the modern PHP 8 syntax and features
 * used in the upgraded Valuestore package.
 */

require_once __DIR__ . '/vendor/autoload.php';

use Spatie\Valuestore\Valuestore;

echo "🚀 Valuestore PHP 8.2+ Example\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Create a temporary file for testing
$tempFile = __DIR__ . '/tests/temp/example.json';

// Ensure the temp directory exists
if (!is_dir(__DIR__ . '/tests/temp')) {
    mkdir(__DIR__ . '/tests/temp', 0755, true);
}

// Clean up if file exists
if (file_exists($tempFile)) {
    unlink($tempFile);
}

// 1. Create a Valuestore
echo "1️⃣  Creating Valuestore...\n";
$store = Valuestore::make($tempFile);
echo "   ✅ Valuestore created\n\n";

// 2. Store single values
echo "2️⃣  Storing single values...\n";
$store->put('name', 'John Doe');
$store->put('age', 30);
$store->put('active', true);
echo "   ✅ Stored: name, age, active\n\n";

// 3. Store multiple values at once (using union type feature)
echo "3️⃣  Storing multiple values (Union Types)...\n";
$store->put([
    'email' => 'john@example.com',
    'country' => 'Thailand',
    'language' => 'th'
]);
echo "   ✅ Stored: email, country, language\n\n";

// 4. Retrieve values
echo "4️⃣  Retrieving values...\n";
echo "   Name: " . $store->get('name') . "\n";
echo "   Age: " . $store->get('age') . "\n";
echo "   Email: " . $store->get('email') . "\n";
echo "   Non-existent (with default): " . $store->get('nonexistent', 'default value') . "\n\n";

// 5. Check if key exists
echo "5️⃣  Checking key existence...\n";
echo "   Has 'name': " . ($store->has('name') ? 'Yes' : 'No') . "\n";
echo "   Has 'missing': " . ($store->has('missing') ? 'Yes' : 'No') . "\n\n";

// 6. Work with arrays
echo "6️⃣  Working with arrays...\n";
$store->push('tags', 'php');
$store->push('tags', 'laravel');
$store->push('tags', 'valuestore');
echo "   Tags: " . json_encode($store->get('tags')) . "\n";

$store->prepend('tags', 'programming');
echo "   Tags (after prepend): " . json_encode($store->get('tags')) . "\n\n";

// 7. Increment and decrement
echo "7️⃣  Increment and decrement...\n";
$store->put('views', 0);
echo "   Initial views: " . $store->get('views') . "\n";

$store->increment('views');
echo "   After increment: " . $store->get('views') . "\n";

$store->increment('views', 5);
echo "   After increment by 5: " . $store->get('views') . "\n";

$store->decrement('views', 2);
echo "   After decrement by 2: " . $store->get('views') . "\n\n";

// 8. Get all values
echo "8️⃣  Getting all values...\n";
$all = $store->all();
echo "   Total keys: " . count($all) . "\n";
echo "   Keys: " . implode(', ', array_keys($all)) . "\n\n";

// 9. Filter keys starting with prefix
echo "9️⃣  Filtering keys...\n";
$store->put('setting_theme', 'dark');
$store->put('setting_language', 'th');
$store->put('setting_timezone', 'Asia/Bangkok');

$settings = $store->allStartingWith('setting_');
echo "   Settings: " . json_encode($settings) . "\n\n";

// 10. ArrayAccess interface (PHP 8 feature)
echo "🔟 Using ArrayAccess interface...\n";
$store['array_key'] = 'array_value';
echo "   Set via array syntax: " . $store['array_key'] . "\n";
echo "   Isset check: " . (isset($store['array_key']) ? 'true' : 'false') . "\n";
unset($store['array_key']);
echo "   After unset: " . (isset($store['array_key']) ? 'exists' : 'removed') . "\n\n";

// 11. Countable interface
echo "1️⃣1️⃣ Using Countable interface...\n";
echo "   Total items: " . count($store) . "\n\n";

// 12. Pull (get and forget)
echo "1️⃣2️⃣ Pull operation (get and forget)...\n";
$pulled = $store->pull('age');
echo "   Pulled value: " . $pulled . "\n";
echo "   Still exists: " . ($store->has('age') ? 'yes' : 'no') . "\n\n";

// 13. Forget specific key
echo "1️⃣3️⃣ Forgetting a key...\n";
$store->forget('active');
echo "   Forgot 'active' key\n";
echo "   Still exists: " . ($store->has('active') ? 'yes' : 'no') . "\n\n";

// 14. Flush keys starting with prefix
echo "1️⃣4️⃣ Flushing keys with prefix...\n";
echo "   Before flush: " . count($store->allStartingWith('setting_')) . " settings\n";
$store->flushStartingWith('setting_');
echo "   After flush: " . count($store->allStartingWith('setting_')) . " settings\n\n";

// 15. Method chaining (using static return type)
echo "1️⃣5️⃣ Method chaining (static return type)...\n";
$store
    ->put('chain1', 'value1')
    ->put('chain2', 'value2')
    ->increment('views')
    ->forget('chain1');
echo "   ✅ Chained multiple operations\n";
echo "   chain2 exists: " . ($store->has('chain2') ? 'yes' : 'no') . "\n";
echo "   chain1 exists: " . ($store->has('chain1') ? 'yes' : 'no') . "\n\n";

// 16. Final state
echo "1️⃣6️⃣ Final state...\n";
echo "   Total items: " . count($store) . "\n";
echo "   All keys: " . implode(', ', array_keys($store->all())) . "\n\n";

// 17. Flush all
echo "1️⃣7️⃣ Flushing all data...\n";
$store->flush();
echo "   ✅ All data cleared\n";
echo "   File exists: " . (file_exists($tempFile) ? 'yes' : 'no (auto-deleted)') . "\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✅ Example completed successfully!\n\n";
echo "📚 Features demonstrated:\n";
echo "   • Typed properties\n";
echo "   • Union types (string|array)\n";
echo "   • Return type declarations\n";
echo "   • Static return type for method chaining\n";
echo "   • Mixed type for flexible parameters\n";
echo "   • ArrayAccess interface\n";
echo "   • Countable interface\n";
echo "   • Modern PHP 8 syntax\n\n";

