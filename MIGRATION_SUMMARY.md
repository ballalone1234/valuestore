# สรุปการอัปเกรด PHP สู่เวอร์ชันล่าสุด

## ภาพรวม
โปรเจกต์นี้ได้รับการอัปเกรดจาก PHP 7.2 เป็น PHP 8.2+ เพื่อใช้ประโยชน์จากฟีเจอร์ใหม่ๆ และปรับปรุงประสิทธิภาพ

## การเปลี่ยนแปลงหลัก

### 1. composer.json
- **PHP Version**: อัปเกรดจาก `^7.2` เป็น `^8.2`
- **PHPUnit**: อัปเกรดจาก `^8.0` เป็น `^11.0` (เวอร์ชันล่าสุด)
- **PHPStan**: คงไว้ที่ `^2.1` (เวอร์ชันล่าสุดอยู่แล้ว)

### 2. src/Valuestore.php

#### Typed Properties (PHP 7.4+)
```php
// เดิม
/** @var string */
protected $fileName;

// ใหม่
protected string $fileName;
```

#### Return Type Declarations
- เพิ่ม return type `static` สำหรับ fluent methods
- เพิ่ม return type `mixed` สำหรับ methods ที่คืนค่าหลายประเภท
- เพิ่ม return type `void` สำหรับ ArrayAccess methods

#### Union Types (PHP 8.0+)
```php
// เดิม
public function put($name, $value = null)

// ใหม่
public function put(string|array $name, mixed $value = null): static
```

#### Nullable Type Syntax (PHP 8.0+)
```php
// เดิม
public static function make(string $fileName, array $values = null)

// ใหม่
public static function make(string $fileName, ?array $values = null): static
```

#### Modern PHP Functions
```php
// เดิม
protected function startsWith(string $haystack, string $needle): bool
{
    return substr($haystack, 0, strlen($needle)) === $needle;
}

// ใหม่
protected function startsWith(string $haystack, string $needle): bool
{
    return str_starts_with($haystack, $needle);
}
```

#### Strict Comparisons
- แทนที่ `!` ด้วย `!` (ไม่มีช่องว่าง) เพื่อความสม่ำเสมอ
- แทนที่ `==` ด้วย `===` สำหรับการเปรียบเทียบที่เข้มงวด
- แทนที่ `! is_null()` ด้วย `!== null`

### 3. tests/ValuestoreTest.php

#### Typed Properties
```php
// เดิม
/** @var string */
protected $storageFile;

/** @var \Spatie\Valuestore\Valuestore */
protected $valuestore;

// ใหม่
protected string $storageFile;
protected Valuestore $valuestore;
```

#### PHP 8 Attributes (แทน Annotations)
```php
// เดิม
/** @test */
public function it_can_store_a_key_value_pair()

// ใหม่
#[Test]
public function it_can_store_a_key_value_pair(): void
```

#### Return Type Declarations
- เพิ่ม `: void` สำหรับทุก test methods

#### PHPUnit 11 Updates
- แทนที่ `assertFileNotExists()` ด้วย `assertFileDoesNotExist()` (deprecated method)
- เพิ่ม `use PHPUnit\Framework\Attributes\Test;` สำหรับ attribute

## ฟีเจอร์ PHP 8+ ที่ใช้

### ✅ Typed Properties (PHP 7.4)
ประกาศ type ของ properties โดยตรง

### ✅ Union Types (PHP 8.0)
รองรับหลาย types ในพารามิเตอร์เดียว (`string|array`)

### ✅ Mixed Type (PHP 8.0)
ใช้สำหรับค่าที่สามารถเป็นประเภทใดก็ได้

### ✅ Nullsafe Operator (PHP 8.0)
ใช้ `?array` แทน `array|null`

### ✅ Attributes (PHP 8.0)
ใช้ `#[Test]` แทน `/** @test */`

### ✅ str_starts_with() (PHP 8.0)
ใช้ฟังก์ชัน built-in แทนการใช้ `substr()`

### ✅ Static Return Type (PHP 8.0)
ใช้ `static` แทน `$this` ใน return type

## ประโยชน์ที่ได้รับ

1. **ประสิทธิภาพดีขึ้น**: PHP 8+ มีความเร็วสูงกว่า PHP 7.x มาก
2. **Type Safety**: Typed properties และ return types ช่วยป้องกัน bugs
3. **Code ที่อ่านง่ายขึ้น**: Attributes และ modern syntax ทำให้โค้ดชัดเจนขึ้น
4. **Maintainability**: Type hints ช่วยให้ IDE แสดง autocomplete ได้ดีขึ้น
5. **Future-proof**: รองรับ PHP 8.2, 8.3, และ 8.4

## การติดตั้งและทดสอบ

```bash
# ติดตั้ง dependencies
composer install

# รัน tests
composer test

# รัน static analysis
vendor/bin/phpstan analyse
```

## ความเข้ากันได้

- **ต้องการ**: PHP 8.2 หรือสูงกว่า
- **รองรับ**: PHP 8.2, 8.3, 8.4
- **ไม่รองรับ**: PHP 7.x และ 8.0, 8.1

## หมายเหตุ

การอัปเกรดนี้เป็น **breaking change** สำหรับผู้ใช้ที่ยังใช้ PHP เวอร์ชันเก่า ควรอัปเดต version ใน composer.json เป็น major version ใหม่ (เช่น จาก 1.x เป็น 2.0)

