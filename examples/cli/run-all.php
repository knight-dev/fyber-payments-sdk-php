#!/usr/bin/env php
<?php
/**
 * Run All Examples
 *
 * Executes all Fyber PHP SDK examples in sequence
 */

echo "\n";
echo "╔═══════════════════════════════════════════════════════════════════╗\n";
echo "║                                                                   ║\n";
echo "║         FYBER PHP SDK - COMPLETE EXAMPLES SUITE                   ║\n";
echo "║                                                                   ║\n";
echo "╚═══════════════════════════════════════════════════════════════════╝\n\n";

$examples = [
    '01-customers-crud.php' => 'Customers CRUD',
    '02-webhooks-test.php' => 'Webhooks Test',
    '03-checkout-session.php' => 'Checkout Session (Hosted)',
    '04-subscriptions.php' => 'Subscriptions',
    '05-installments.php' => 'Installment Plans (BNPL)',
];

$results = [];
$totalExamples = count($examples);
$passed = 0;
$failed = 0;

foreach ($examples as $file => $description) {
    $num = substr($file, 0, 2);
    echo "═══════════════════════════════════════════════════════════════════\n";
    echo "  Running Example {$num}: {$description}\n";
    echo "═══════════════════════════════════════════════════════════════════\n\n";

    $scriptPath = __DIR__ . '/' . $file;

    if (!file_exists($scriptPath)) {
        echo "  ⚠ File not found: {$file}\n\n";
        $results[$file] = ['status' => 'skipped', 'message' => 'File not found'];
        continue;
    }

    $startTime = microtime(true);

    // Execute the example
    $output = [];
    $returnCode = 0;
    exec("php \"{$scriptPath}\" 2>&1", $output, $returnCode);

    $duration = round((microtime(true) - $startTime) * 1000);

    // Display output
    foreach ($output as $line) {
        echo "  {$line}\n";
    }

    if ($returnCode === 0) {
        $passed++;
        $results[$file] = ['status' => 'passed', 'duration' => $duration];
        echo "\n  ✓ Example {$num} completed successfully ({$duration}ms)\n\n";
    } else {
        $failed++;
        $results[$file] = ['status' => 'failed', 'duration' => $duration];
        echo "\n  ✗ Example {$num} failed (exit code: {$returnCode})\n\n";
    }

    // Small delay between examples
    usleep(500000); // 500ms
}

// Summary
echo "\n";
echo "╔═══════════════════════════════════════════════════════════════════╗\n";
echo "║                         RESULTS SUMMARY                           ║\n";
echo "╠═══════════════════════════════════════════════════════════════════╣\n";

foreach ($examples as $file => $description) {
    $result = $results[$file] ?? ['status' => 'unknown'];
    $status = $result['status'];
    $icon = match($status) {
        'passed' => '✓',
        'failed' => '✗',
        'skipped' => '⚠',
        default => '?',
    };
    $duration = isset($result['duration']) ? " ({$result['duration']}ms)" : '';
    $num = substr($file, 0, 2);
    printf("║  %s  %s. %-45s %s\n", $icon, $num, $description, $duration);
}

echo "╠═══════════════════════════════════════════════════════════════════╣\n";
printf("║  Total: %d | Passed: %d | Failed: %d                              ║\n",
    $totalExamples, $passed, $failed);
echo "╚═══════════════════════════════════════════════════════════════════╝\n\n";

// Exit with appropriate code
exit($failed > 0 ? 1 : 0);
