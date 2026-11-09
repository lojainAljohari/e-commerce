# E-Commerce Project -- Setup & Testing Guide

# دليل تشغيل واختبار مشروع التجارة الإلكترونية

## 1. Project Setup \| إعداد المشروع وتشغيله

### Step 1: Generate Laravel Application Key

Run the following command in the project root:

``` bash
docker run --rm -v "${PWD}:/app" -w /app php:8.3-cli php artisan key:generate --show
```

Copy the generated key into the `.env` file under `APP_KEY`.

**العربية:** نفّذ الأمر لتوليد مفتاح Laravel، ثم انسخ المفتاح الناتج وضعه
في ملف `.env` أمام `APP_KEY`.

### Step 2: Install and Build Warehouse Service

``` bash
docker run --rm -v "${PWD}/warehouse_service:/app" -w /app node:22-bookworm-slim sh -c "npm install && npm run build"
```

**العربية:** تثبيت المكتبات المطلوبة وبناء خدمة المستودع (Warehouse
Service).

### Step 3: Build and Start Docker Containers

``` bash
docker compose up -d --build
```

**العربية:** بناء وتشغيل جميع الخدمات والحاويات باستخدام Docker.

### Step 4: Run Database Migrations and Seeders

``` bash
docker compose exec app php artisan migrate --seed
```

**العربية:** تنفيذ الـ Migrations والـ Seeders لإنشاء الجداول وإضافة
البيانات الأولية.

### Step 5: Insert Initial Stock Data

``` bash
docker compose exec warehouse_db mysql -uroot -proot warehouse -e "INSERT INTO stock (productId, quantity) VALUES (1,10),(2,5),(3,1) ON DUPLICATE KEY UPDATE quantity = VALUES(quantity);"
```

**العربية:** إضافة كميات أولية للمخزون:

-   Product 1: 10 units
-   Product 2: 5 units
-   Product 3: 1 unit

------------------------------------------------------------------------

## 2. Open the Application \| فتح المشروع

Open your browser and visit:

**E-Commerce Application:**

http://localhost:8000

**MySQL Database Management Interface:**

http://localhost:8080

**العربية:** افتح المتصفح واستخدم الروابط السابقة لفتح التطبيق وواجهة
إدارة قاعدة البيانات.

------------------------------------------------------------------------

## 3. Postman Testing -- Order and Stock Management

## اختبار الطلبات وإدارة المخزون باستخدام Postman

### Step 1: Login as a Regular User

Open the `Log In` request under the User collection.

Use the following credentials:

``` text
Email: user@ecommerce.com
Password: password
```

**العربية:** سجّل الدخول باستخدام حساب المستخدم العادي للحصول على Token
واستخدامه في الطلبات التالية.

### Step 2: Check Product Availability

Execute:

`Get Category Products`

The API should return the products along with their available stock
quantities.

**العربية:** اعرض المنتجات من خلال API وتأكد من ظهور الكميات المتاحة لكل
منتج.

### Step 3: Create an Order

Execute:

`Add Order`

Create an order containing Product ID `3`.

The initial quantity of Product 3 is `1`.

**Expected Result:**

After successfully creating the order, the available stock of Product 3
should become `0`.

**العربية:**

أنشئ طلباً يحتوي على المنتج رقم 3، والذي يمتلك كمية واحدة فقط.

بعد نجاح الطلب، يجب أن تصبح الكمية المتاحة للمنتج `0`.

### Step 4: Verify Stock Deduction

Execute `Get Category Products` again.

Verify that Product 3 now has:

``` text
Available Quantity: 0
```

**العربية:** أعد عرض المنتجات وتأكد من أن كمية المنتج الثالث أصبحت صفراً
بعد إنشاء الطلب.

### Step 5: Login as Admin

Log in using the Admin account.

Then execute the request that retrieves all existing orders.

Find the order whose status is:

``` text
pending
```

Copy its Order ID.

**العربية:** سجّل الدخول كمسؤول (Admin)، ثم اعرض جميع الطلبات وابحث عن
الطلب الذي حالته `pending`، وانسخ رقم الـ ID الخاص به.

### Step 6: Cancel the Order

Open:

`Update Order Statuses`

Enter the copied Order ID and update the request body to:

``` json
{
  "status": "cancelled"
}
```

**Expected Result:**

-   The order status should become `cancelled`.
-   The reserved stock should be returned to the warehouse.
-   Product 3 quantity should return to `1`.
-   Any other reserved product quantities should also be restored.

**العربية:**

غيّر حالة الطلب إلى `cancelled`.

يجب أن تتم إعادة جميع الكميات المحجوزة إلى المخزون، بما فيها المنتج
الثالث الذي يجب أن تعود كميته إلى `1`.

### Step 7: Verify Stock Restoration

Execute `Get Category Products` again.

Confirm that the stock quantities have been restored correctly.

**العربية:** أعد عرض المنتجات مرة أخرى وتأكد من استعادة الكميات إلى
قيمها السابقة.

At this point, the order cancellation and stock restoration process
should work correctly.

------------------------------------------------------------------------

## 4. Warehouse Service Failure Testing

## اختبار توقف خدمة المستودع

Open Docker Desktop.

Find the `e-commerce` project and locate the following containers:

-   `warehouse`
-   `warehouse_db`

Stop both containers.

**العربية:** افتح Docker Desktop، ثم ادخل إلى مشروع `e-commerce` وأوقف
الحاويتين `warehouse` و`warehouse_db`.

After stopping the services, return to Postman and try executing:

`Get Category Products`

**Expected Result:**

The request should fail because the Warehouse Service is unavailable.

This verifies that the application cannot retrieve product stock
information when the warehouse service is down.

**العربية:**

بعد إيقاف الخدمتين، حاول عرض المنتجات من خلال Postman.

من المفترض أن يفشل الطلب بسبب توقف خدمة المستودع وعدم إمكانية الوصول إلى
بيانات المخزون.

------------------------------------------------------------------------

## 5. Race Condition Testing

## اختبار مشكلة Race Condition عند تنفيذ طلبات متزامنة

This test checks whether the system correctly handles multiple
simultaneous orders for a product with limited stock.

**العربية:** يهدف هذا الاختبار إلى التأكد من أن النظام يتعامل بشكل صحيح
مع الطلبات المتزامنة عندما تكون كمية المنتج محدودة.

### Step 1: Prepare the Environment

Make sure the Docker containers are running again.

Product 3 must have:

``` text
Product ID: 3
Available Quantity: 1
```

### Step 2: Run the Race Condition Script

Open the VS Code terminal inside the project root and execute:

``` bash
docker run --rm --network e-commerce_default -v "${PWD}:/app" -w /app php:8.3-fpm-bookworm php race_all.php
```

This command runs the `race_all.php` script located in the project root.

**العربية:**

هذا الأمر يشغّل سكربت `race_all.php` الموجود داخل المشروع، والذي يقوم
بإرسال عدة طلبات شراء في الوقت نفسه لمحاكاة حدوث Race Condition.

### Step 3: Understand the Test Scenario

The script sends 8 simultaneous order requests for Product ID `3`.

The product has only 1 available unit.

**Expected Result:**

-   Only one order should succeed.
-   The remaining seven orders should be rejected because the stock is
    insufficient.
-   The final available stock quantity should be `0`.
-   No negative stock quantities should occur.

**العربية:**

يقوم السكربت بإرسال 8 طلبات متزامنة على المنتج رقم 3، مع العلم أن الكمية
المتاحة منه هي وحدة واحدة فقط.

النتيجة المتوقعة:

-   نجاح طلب واحد فقط.
-   رفض الطلبات السبعة المتبقية بسبب عدم توفر الكمية.
-   تصبح الكمية النهائية `0`.
-   يجب ألا تصبح كمية المخزون سالبة.

------------------------------------------------------------------------

## 6. Additional Stock Validation Test

## اختبار إضافي للتحقق من توفر جميع المنتجات

There is another important scenario to test:

If an order contains multiple products, and at least one product has
zero available stock, the entire order should be rejected.

Even if the other products have sufficient stock, the system must not
create a partial order.

**Expected Result:**

-   The order should not be created.
-   No stock should be deducted from any product.
-   The API response should indicate that the required stock is
    unavailable.

**العربية:**

يوجد اختبار إضافي مهم:

إذا كان الطلب يحتوي على عدة منتجات، وكان أحد هذه المنتجات لا يمتلك أي
كمية متاحة، فيجب رفض الطلب بالكامل.

حتى لو كانت بقية المنتجات تمتلك كميات كافية، يجب ألا يتم إنشاء طلب جزئي.

النتيجة المتوقعة:

-   عدم إنشاء الطلب.
-   عدم خصم أي كمية من بقية المنتجات.
-   إرجاع رسالة في الـ API Response توضح عدم توفر الكمية المطلوبة.

------------------------------------------------------------------------

## 7. Final Expected Results \| النتائج النهائية المتوقعة

  Test                     Expected Result
  ------------------------ --------------------------
  Initial Stock            Product 3 = 1
  Create Order             Stock becomes 0
  Cancel Order             Stock returns to 1
  Stop Warehouse Service   Product request fails
  8 Simultaneous Orders    Only 1 succeeds
  Insufficient Stock       Entire order is rejected
  Negative Stock           Must never occur

### Final Verification

**العربية:** إذا نجحت جميع الاختبارات السابقة، فهذا يعني أن النظام
يتعامل بشكل صحيح مع:

1.  حجز كميات المنتجات عند إنشاء الطلب.
2.  إعادة الكميات عند إلغاء الطلب.
3.  التعامل مع توقف خدمة المستودع.
4.  منع حدوث Race Condition عند تنفيذ الطلبات المتزامنة.
5.  منع إنشاء طلبات تحتوي على منتجات غير متوفرة بالكامل.
6.  الحفاظ على سلامة بيانات المخزون ومنع الكميات السالبة.

**English:** Successful completion of these tests demonstrates correct
inventory reservation, stock restoration, service failure handling,
concurrent order processing, and stock consistency.
