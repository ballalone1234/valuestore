#!/usr/bin/env php
<?php

/**
 * PHP Version Compatibility Checker
 * 
 * สคริปต์นี้ตรวจสอบว่าระบบของคุณพร้อมสำหรับ PHP 8.2+ หรือไม่
 */

echo "🔍 กำลังตรวจสอบความพร้อมของระบบ...\n\n";

// ตรวจสอบเวอร์ชัน PHP
$currentVersion = PHP_VERSION;
$requiredVersion = '8.2.0';
$isCompatible = version_compare($currentVersion, $requiredVersion, '>=');

echo "📦 เวอร์ชัน PHP ปัจจุบัน: {$currentVersion}\n";
echo "✅ เวอร์ชันที่ต้องการ: {$requiredVersion} หรือสูงกว่า\n\n";

if (!$isCompatible) {
    echo "❌ เวอร์ชัน PHP ของคุณต่ำเกินไป!\n";
    echo "   กรุณาอัปเกรด PHP เป็นเวอร์ชัน {$requiredVersion} หรือสูงกว่า\n\n";
    exit(1);
}

echo "✅ เวอร์ชัน PHP ของคุณรองรับแล้ว!\n\n";

// ตรวจสอบฟีเจอร์ที่จำเป็น
echo "🔧 ตรวจสอบฟีเจอร์ที่จำเป็น:\n\n";

$features = [
    'Typed Properties' => version_compare(PHP_VERSION, '7.4.0', '>='),
    'Union Types' => version_compare(PHP_VERSION, '8.0.0', '>='),
    'Mixed Type' => version_compare(PHP_VERSION, '8.0.0', '>='),
    'Attributes' => version_compare(PHP_VERSION, '8.0.0', '>='),
    'str_starts_with()' => function_exists('str_starts_with'),
    'str_ends_with()' => function_exists('str_ends_with'),
    'str_contains()' => function_exists('str_contains'),
    'JSON Extension' => extension_loaded('json'),
];

$allSupported = true;
foreach ($features as $feature => $supported) {
    $status = $supported ? '✅' : '❌';
    echo "  {$status} {$feature}\n";
    if (!$supported) {
        $allSupported = false;
    }
}

echo "\n";

if (!$allSupported) {
    echo "❌ บางฟีเจอร์ไม่รองรับ กรุณาอัปเกรด PHP\n\n";
    exit(1);
}

// ตรวจสอบ Composer
echo "📦 ตรวจสอบ Composer:\n\n";

$composerPath = null;
$composerCommands = ['composer', 'composer.phar'];

foreach ($composerCommands as $cmd) {
    $output = shell_exec("{$cmd} --version 2>&1");
    if ($output && stripos($output, 'Composer') !== false) {
        $composerPath = $cmd;
        break;
    }
}

if ($composerPath) {
    echo "  ✅ Composer พบแล้ว: {$composerPath}\n";
    $version = shell_exec("{$composerPath} --version 2>&1");
    echo "     " . trim($version) . "\n";
} else {
    echo "  ⚠️  ไม่พบ Composer (อาจจะติดตั้งแล้วแต่ไม่อยู่ใน PATH)\n";
}

echo "\n";

// ตรวจสอบ Extensions ที่แนะนำ
echo "🔌 Extensions ที่แนะนำ:\n\n";

$recommendedExtensions = [
    'json' => 'จำเป็นสำหรับการทำงานกับ JSON',
    'mbstring' => 'แนะนำสำหรับการทำงานกับ Unicode',
    'fileinfo' => 'แนะนำสำหรับการตรวจสอบไฟล์',
];

foreach ($recommendedExtensions as $ext => $description) {
    $loaded = extension_loaded($ext);
    $status = $loaded ? '✅' : '⚠️ ';
    echo "  {$status} {$ext} - {$description}\n";
}

echo "\n";

// สรุปผลการตรวจสอบ
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📊 สรุปผลการตรวจสอบ:\n\n";

if ($isCompatible && $allSupported) {
    echo "✅ ระบบของคุณพร้อมสำหรับ Valuestore PHP 8.2+ แล้ว!\n\n";
    echo "ขั้นตอนถัดไป:\n";
    echo "  1. รัน: composer install\n";
    echo "  2. รัน: composer test\n";
    echo "  3. เริ่มใช้งาน Valuestore!\n\n";
    exit(0);
} else {
    echo "❌ ระบบของคุณยังไม่พร้อม กรุณาแก้ไขปัญหาที่พบ\n\n";
    exit(1);
}

