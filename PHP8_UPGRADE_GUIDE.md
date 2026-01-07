# คู่มือการอัปเกรดสู่ PHP 8.2+

## สำหรับผู้พัฒนา

### ความต้องการของระบบ

```json
{
  "require": {
    "php": "^8.2"
  }
}
```

### การติดตั้ง

```bash
composer require spatie/valuestore
```

หากคุณใช้ PHP เวอร์ชันเก่า (7.2-8.1) กรุณาอัปเกรด PHP ก่อน หรือใช้เวอร์ชันเก่าของ package นี้

### การตรวจสอบเวอร์ชัน PHP

```bash
php -v
```

ควรแสดงผลลัพธ์เป็น PHP 8.2.x หรือสูงกว่า

## การเปลี่ยนแปลงที่สำคัญ

### 1. Typed Properties

คลาส `Valuestore` ใช้ typed properties แล้ว:

```php
// ตัวอย่างการใช้งาน - ไม่มีการเปลี่ยนแปลง API
$valuestore = Valuestore::make('settings.json');
$valuestore->put('key', 'value');
```

### 2. Return Types

ทุก methods มี return type declarations แล้ว ซึ่งช่วยให้:
- IDE แสดง autocomplete ได้ดีขึ้น
- ป้องกัน type errors ได้ดีขึ้น
- Code ชัดเจนขึ้น

```php
// Return type 'static' สำหรับ method chaining
$valuestore
    ->put('key1', 'value1')
    ->put('key2', 'value2')
    ->flush();
```

### 3. Union Types

Method `put()` รองรับ union types:

```php
// ส่ง string และ value
$valuestore->put('key', 'value');

// หรือส่ง array
$valuestore->put(['key1' => 'value1', 'key2' => 'value2']);
```

### 4. Modern PHP Functions

ใช้ฟังก์ชัน PHP 8 built-in:
- `str_starts_with()` แทน `substr()`
- Strict type comparisons (`===` แทน `==`)

## ตัวอย่างการใช้งาน

### การสร้าง Valuestore

```php
use Spatie\Valuestore\Valuestore;

// สร้างแบบว่างเปล่า
$valuestore = Valuestore::make('settings.json');

// สร้างพร้อมข้อมูลเริ่มต้น
$valuestore = Valuestore::make('settings.json', [
    'theme' => 'dark',
    'language' => 'th'
]);
```

### การเก็บและดึงข้อมูล

```php
// เก็บค่าเดียว
$valuestore->put('name', 'John');

// เก็บหลายค่า
$valuestore->put([
    'name' => 'John',
    'email' => 'john@example.com',
    'age' => 30
]);

// ดึงค่า
$name = $valuestore->get('name'); // 'John'

// ดึงค่าพร้อม default
$country = $valuestore->get('country', 'Thailand'); // 'Thailand'
```

### การทำงานกับ Arrays

```php
// Push ค่าเข้า array
$valuestore->push('tags', 'php');
$valuestore->push('tags', 'laravel');
// Result: ['php', 'laravel']

// Prepend ค่าเข้า array
$valuestore->prepend('tags', 'coding');
// Result: ['coding', 'php', 'laravel']
```

### การเพิ่มและลดค่า

```php
// Increment
$valuestore->put('views', 0);
$valuestore->increment('views'); // 1
$valuestore->increment('views', 5); // 6

// Decrement
$valuestore->decrement('views'); // 5
$valuestore->decrement('views', 2); // 3
```

### ArrayAccess Interface

```php
// ใช้เหมือน array
$valuestore['key'] = 'value';
$value = $valuestore['key'];
unset($valuestore['key']);

if (isset($valuestore['key'])) {
    // ...
}
```

### Countable Interface

```php
$count = count($valuestore); // จำนวน keys ทั้งหมด
```

### การลบข้อมูล

```php
// ลบ key เดียว
$valuestore->forget('key');

// ลบทั้งหมด
$valuestore->flush();

// ลบ keys ที่ขึ้นต้นด้วย prefix
$valuestore->flushStartingWith('temp_');
```

### การดึงและลบพร้อมกัน

```php
$value = $valuestore->pull('key'); // ดึงค่าและลบ key ทันที
```

### การกรองข้อมูล

```php
// ดึงทุก keys
$all = $valuestore->all();

// ดึง keys ที่ขึ้นต้นด้วย prefix
$settings = $valuestore->allStartingWith('setting_');
```

## การทดสอบ

### รัน Unit Tests

```bash
composer test
```

### รัน Static Analysis

```bash
vendor/bin/phpstan analyse
```

## Best Practices

### 1. ใช้ Type Hints

```php
function saveSettings(Valuestore $store): void
{
    $store->put('updated_at', time());
}
```

### 2. ใช้ Method Chaining

```php
$valuestore
    ->put('key1', 'value1')
    ->put('key2', 'value2')
    ->increment('counter');
```

### 3. ตรวจสอบก่อนใช้

```php
if ($valuestore->has('key')) {
    $value = $valuestore->get('key');
}
```

### 4. ใช้ Default Values

```php
// ดีกว่าการตรวจสอบ null
$theme = $valuestore->get('theme', 'light');
```

## Performance Tips

1. **Batch Operations**: ใช้ `put()` กับ array แทนการเรียกหลายครั้ง
   ```php
   // ดี
   $valuestore->put(['key1' => 'val1', 'key2' => 'val2']);
   
   // ไม่ค่อยดี
   $valuestore->put('key1', 'val1');
   $valuestore->put('key2', 'val2');
   ```

2. **Minimize File I/O**: Cache ค่าที่ใช้บ่อยในตัวแปร
   ```php
   $all = $valuestore->all(); // อ่านไฟล์ครั้งเดียว
   $value1 = $all['key1'];
   $value2 = $all['key2'];
   ```

3. **Use Appropriate Methods**: ใช้ `has()` แทน `get()` เมื่อต้องการแค่ตรวจสอบ
   ```php
   // ดี
   if ($valuestore->has('key')) { ... }
   
   // ไม่ค่อยดี
   if ($valuestore->get('key') !== null) { ... }
   ```

## Troubleshooting

### ปัญหา: PHP Version Mismatch

```
Your requirements could not be resolved to an installable set of packages.
Problem 1
  - spatie/valuestore requires php ^8.2
```

**แก้ไข**: อัปเกรด PHP เป็นเวอร์ชัน 8.2 หรือสูงกว่า

### ปัญหา: Type Errors

```
TypeError: Return value must be of type static, null returned
```

**แก้ไข**: ตรวจสอบว่าคุณใช้ methods ถูกต้อง และไม่ได้แก้ไขคลาสโดยตรง

### ปัญหา: File Permissions

```
Warning: file_put_contents(): failed to open stream
```

**แก้ไข**: ตรวจสอบสิทธิ์การเขียนไฟล์

```bash
chmod 755 storage/
```

## Migration จาก PHP 7.x

หากคุณกำลังอัปเกรดจาก PHP 7.x:

1. อัปเกรด PHP เป็น 8.2+
2. อัปเดต composer dependencies
3. ทดสอบ application ทั้งหมด
4. ตรวจสอบ deprecated features

```bash
# อัปเดต dependencies
composer update

# รัน tests
composer test
```

## สนับสนุน

- **Documentation**: [README.md](README.md)
- **Issues**: GitHub Issues
- **PHP 8 Migration Guide**: https://www.php.net/manual/en/migration82.php

## สรุป

การอัปเกรดสู่ PHP 8.2+ ทำให้:
- ✅ โค้ดปลอดภัยขึ้นด้วย type safety
- ✅ ประสิทธิภาพดีขึ้น
- ✅ Developer experience ดีขึ้น
- ✅ รองรับฟีเจอร์ใหม่ๆ ของ PHP

API ยังคงเหมือนเดิม ไม่ต้องเปลี่ยนโค้ดที่ใช้งานอยู่!

