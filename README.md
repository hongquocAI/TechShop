# TechShop — Website thương mại điện tử phụ kiện công nghệ tích hợp quản lý dữ liệu sản phẩm (PIM) và thanh toán trực tuyến

> **Đề tài:** *Xây dựng website thương mại điện tử và quản lý dữ liệu sản phẩm (PIM) tích hợp thanh toán trực tuyến*
> **Học phần:** Lập trình mã nguồn mở
> **Công nghệ:** Laravel 13 · PHP 8.3 · MySQL 8 · Blade · Tailwind (CDN)
> **Tác giả:** [hongquocAI](https://github.com/hongquocAI)

---

## Mục lục
1. [Giới thiệu](#1-giới-thiệu)
2. [Tính năng](#2-tính-năng)
3. [Công nghệ sử dụng](#3-công-nghệ-sử-dụng)
4. [Yêu cầu hệ thống](#4-yêu-cầu-hệ-thống)
5. [Cài đặt và chạy (từng bước)](#5-cài-đặt-và-chạy-từng-bước)
6. [Tài khoản demo](#6-tài-khoản-demo)
7. [Hướng dẫn sử dụng và kịch bản demo](#7-hướng-dẫn-sử-dụng-và-kịch-bản-demo)
8. [Cấu hình `.env`](#8-cấu-hình-env)
9. [Thanh toán VNPay](#9-thanh-toán-vnpay)
10. [Cấu trúc cơ sở dữ liệu](#10-cấu-trúc-cơ-sở-dữ-liệu)
11. [Cấu trúc thư mục](#11-cấu-trúc-thư-mục)
12. [Danh sách route](#12-danh-sách-route)
13. [Các điểm kỹ thuật đáng chú ý](#13-các-điểm-kỹ-thuật-đáng-chú-ý)
14. [Nhập/xuất CSV](#14-nhậpxuất-csv)
15. [Lệnh artisan hữu ích](#15-lệnh-artisan-hữu-ích)
16. [Xử lý sự cố thường gặp](#16-xử-lý-sự-cố-thường-gặp)
17. [Phạm vi và hướng phát triển](#17-phạm-vi-và-hướng-phát-triển)

---

## 1. Giới thiệu

**TechShop** là website bán phụ kiện công nghệ (tai nghe, sạc & cáp, chuột & bàn phím, pin dự phòng) được xây dựng bằng **Laravel/PHP**. Dự án ghép ba mảng thành một hệ thống hoàn chỉnh:

| Trụ cột | Nội dung |
|---|---|
| **Thương mại điện tử** | Trang chủ, danh mục, tìm kiếm, lọc theo hãng/giá/thuộc tính, giỏ hàng, đặt hàng, tài khoản khách, quản lý đơn hàng. |
| **Quản lý dữ liệu sản phẩm (PIM)** | Thuộc tính động theo từng danh mục, thanh % hoàn thiện dữ liệu, quy trình duyệt Nháp → Chờ duyệt → Đang bán, nhập/xuất CSV hàng loạt. |
| **Thanh toán trực tuyến** | COD và VNPay (chữ ký HMAC-SHA512, Return URL + IPN, chống xử lý trùng, tự hủy đơn quá hạn). Có cổng giả lập để demo khi chưa có tài khoản VNPay sandbox. |

Dự án cố ý được **rút gọn** (9 bảng) để dễ đọc, dễ chạy và dễ bảo vệ, nhưng vẫn giữ đủ các cơ chế quan trọng của một hệ thống thật: khóa dòng chống bán vượt tồn kho, snapshot giá, state machine đơn hàng, idempotency khi thanh toán.

## 2. Tính năng

### 2.1 Khách hàng (Storefront)
- Trang chủ: 8 sản phẩm mới nhất còn hàng và danh sách danh mục kèm số sản phẩm.
- Danh sách sản phẩm với **tìm kiếm**, **lọc** theo thương hiệu, khoảng giá và **thuộc tính động** (trong trang danh mục, chỉ những thuộc tính được đánh dấu "có thể lọc"), **sắp xếp** theo giá tăng/giảm hoặc mới nhất.
- Trang chi tiết: ảnh, giá gốc/giá khuyến mãi, tồn kho, bảng thông số kỹ thuật (từ thuộc tính động), sản phẩm liên quan.
- **Giỏ hàng** lưu trong session: thêm, sửa số lượng, xóa. Phí ship 30.000đ, **miễn phí từ 500.000đ**.
- **Thanh toán** (khách vãng lai hoặc đã đăng nhập): chọn COD hoặc VNPay.
- Đăng ký / đăng nhập / đăng xuất, xem **lịch sử đơn hàng**, **tự hủy** đơn chưa thanh toán.

### 2.2 Quản trị (Admin, đường dẫn `/admin`)
- **Dashboard:** doanh thu, đơn theo trạng thái, top sản phẩm bán chạy, sản phẩm sắp hết hàng, tỉ lệ thanh toán theo cổng, danh sách sản phẩm dữ liệu chưa hoàn thiện.
- **Sản phẩm (PIM):** thêm/sửa/xóa mềm, upload ảnh (tối đa 2 MB), form tự đổi các ô thuộc tính theo danh mục đã chọn, hiển thị % hoàn thiện, đổi trạng thái theo quy trình duyệt.
- **Danh mục / Thương hiệu / Thuộc tính:** quản lý trong trang Catalog; thuộc tính có kiểu `text`/`number`/`select`, đánh dấu bắt buộc và có thể lọc.
- **Đơn hàng:** xem chi tiết, đổi trạng thái theo state machine; hủy đơn tự động hoàn kho.
- **Nhập/Xuất CSV** sản phẩm kèm thuộc tính.

### 2.3 Thanh toán
- **COD:** đặt hàng xong ở trạng thái `unpaid`, thanh toán khi nhận hàng.
- **VNPay:** tạo URL có chữ ký → chuyển sang cổng → nhận kết quả qua **Return URL** và **IPN** → xác minh chữ ký, đối chiếu số tiền, ghi nhận đúng **một lần**.
- **Cổng giả lập (mock):** nếu `VNP_TMN_CODE` để trống, hệ thống chuyển tới trang mô phỏng có nút *Thành công* / *Thất bại* để demo toàn bộ luồng mà không cần đăng ký.
- Đơn VNPay quá **15 phút** chưa trả tiền sẽ bị tự hủy và hoàn tồn kho.

## 3. Công nghệ sử dụng

| Thành phần | Công nghệ |
|---|---|
| Ngôn ngữ / Framework | PHP 8.3, Laravel 13 |
| Cơ sở dữ liệu | MySQL 8 (ORM Eloquent, migration, seeder) |
| Giao diện | Blade template, Tailwind CSS (qua CDN, không cần build) |
| Xác thực | Laravel Auth (session), middleware phân quyền `AdminOnly` |
| Thanh toán | VNPay v2.1.0 (HMAC-SHA512) + cổng giả lập |
| Lập lịch | Laravel Scheduler (`orders:cancel-expired`) |

> Giao diện dùng CDN nên **không cần** `npm install` hay `npm run dev`. Máy cần có Internet khi mở web để tải Tailwind.

## 4. Yêu cầu hệ thống

- **PHP ≥ 8.2** (đã thử với 8.3) với các extension: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `curl`, `intl`, `zip`.
- **Composer 2.x**
- **MySQL 8** hoặc MariaDB 10.4+
- Git

Cách dễ nhất trên Windows: cài **[Laragon](https://laragon.org/)** (có sẵn PHP, Composer, MySQL). Trên macOS có thể dùng Laravel Herd; trên Linux dùng gói `php`, `composer`, `mysql-server`.

## 5. Cài đặt và chạy (từng bước)

### Bước 1 — Clone mã nguồn
```bash
git clone https://github.com/hongquocAI/TechShop.git
cd TechShop
```

### Bước 2 — Cài thư viện PHP
```bash
composer install
```

### Bước 3 — Tạo file cấu hình
```bash
# Windows (PowerShell / CMD)
copy .env.example .env
# macOS / Linux
cp .env.example .env
```
Mở `.env` và chỉnh phần database cho khớp máy bạn (xem [mục 8](#8-cấu-hình-env)):
```env
APP_NAME=TechShop
APP_URL=http://localhost:8000
APP_LOCALE=vi
APP_TIMEZONE=Asia/Ho_Chi_Minh

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=techshop
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

### Bước 4 — Tạo database
Tạo một database rỗng tên `techshop` (bảng mã `utf8mb4`):
```sql
CREATE DATABASE techshop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```
Có thể chạy trong HeidiSQL / phpMyAdmin / MySQL Workbench, hoặc dòng lệnh:
```bash
mysql -u root -p -e "CREATE DATABASE techshop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### Bước 5 — Sinh khóa ứng dụng, tạo bảng và dữ liệu mẫu
```bash
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
```
Sau lệnh này DB có sẵn 4 danh mục, 5 thương hiệu, 13 sản phẩm mẫu và 2 tài khoản demo.

### Bước 6 — Chạy web
```bash
php artisan serve
```
Mở **http://localhost:8000**. Trang quản trị: **http://localhost:8000/admin**.

### Bước 7 (tuỳ chọn) — Bật tự hủy đơn quá hạn
Mở thêm một cửa sổ terminal:
```bash
php artisan schedule:work
```
Hoặc chạy tay khi cần: `php artisan orders:cancel-expired`.

### Cách chạy nhanh trên Windows + Laragon
Nếu MySQL của Laragon chạy ở cổng khác 3306 (ví dụ máy có sẵn MySQL cài riêng), sửa `DB_PORT` trong `.env` cho đúng. Tệp [`run.bat`](run.bat) là ví dụ khởi động MySQL của Laragon ở cổng `3307` rồi bật web; hãy sửa đường dẫn trong đó cho khớp máy bạn trước khi dùng.

## 6. Tài khoản demo

| Vai trò | Email | Mật khẩu |
|---|---|---|
| Quản trị viên | `admin@techshop.test` | `password` |
| Khách hàng | `khach@techshop.test` | `password` |

> Chỉ dùng cho môi trường học tập/demo. Đổi mật khẩu nếu triển khai thật.

## 7. Hướng dẫn sử dụng và kịch bản demo

Kịch bản gợi ý (khoảng 5–7 phút) để trình bày với giảng viên:

**A. Mua hàng bằng COD**
1. Vào `/` → chọn một sản phẩm → **Thêm vào giỏ**.
2. Vào **Giỏ hàng**, đổi số lượng. Quan sát phí ship (miễn phí khi tổng ≥ 500.000đ).
3. **Thanh toán** → nhập tên, SĐT, địa chỉ → chọn **COD** → Đặt hàng. Nhận mã đơn dạng `DHyymmddXXXXX`.

**B. Mua hàng bằng VNPay (giả lập)**
1. Làm như trên nhưng chọn **VNPay**.
2. Hệ thống chuyển tới trang cổng giả lập → bấm **Thành công**. Đơn chuyển `paid` + `confirmed`. Thử lại và bấm **Thất bại** để thấy `payment_status = failed`.

**C. Quản lý sản phẩm (PIM)**
1. Đăng nhập admin → **Sản phẩm → Thêm mới**.
2. Chọn danh mục "Tai nghe": form hiện ô *Kết nối, Thời lượng pin, Chống ồn*. Đổi sang "Sạc & cáp": bộ ô thuộc tính đổi theo.
3. Lưu dạng **Nháp**, bỏ trống thuộc tính bắt buộc → thanh **% hoàn thiện** thấp. Thử chuyển **Đang bán** → bị chặn vì thiếu dữ liệu bắt buộc. Điền đủ → xuất bản được và sản phẩm xuất hiện ngoài storefront.
4. Sản phẩm mẫu *"Tai nghe mẫu (nháp, thiếu dữ liệu)"* có sẵn để minh họa.

**D. Quản lý đơn hàng**
1. **Admin → Đơn hàng** → mở một đơn. Chỉ hiện các trạng thái kế tiếp hợp lệ.
2. Thử **Hủy** đơn: tồn kho của sản phẩm được cộng trả lại.

**E. Nhập/xuất CSV**
1. **Admin → Nhập/Xuất** → tải file CSV hiện có, sửa giá/tồn trong Excel, nhập lại → sản phẩm được cập nhật theo SKU.

**F. Dashboard**
- **Admin → Dashboard** xem doanh thu, đơn theo trạng thái, top bán chạy, sắp hết hàng.

## 8. Cấu hình `.env`

| Biến | Ý nghĩa | Giá trị mẫu |
|---|---|---|
| `APP_NAME` | Tên site | `TechShop` |
| `APP_KEY` | Khóa mã hóa, sinh bằng `php artisan key:generate` | tự sinh |
| `APP_URL` | URL gốc (dùng sinh Return URL của VNPay) | `http://localhost:8000` |
| `APP_LOCALE` | Ngôn ngữ | `vi` |
| `APP_TIMEZONE` | Múi giờ (ảnh hưởng thời hạn thanh toán 15 phút) | `Asia/Ho_Chi_Minh` |
| `DB_CONNECTION` | Loại DB | `mysql` |
| `DB_HOST` / `DB_PORT` | Máy chủ và cổng MySQL | `127.0.0.1` / `3306` |
| `DB_DATABASE` | Tên database | `techshop` |
| `DB_USERNAME` / `DB_PASSWORD` | Tài khoản MySQL | `root` / (trống) |
| `SESSION_DRIVER` | Nơi lưu session (giỏ hàng nằm ở đây) | `file` |
| `CACHE_STORE` | Cache | `file` |
| `QUEUE_CONNECTION` | Hàng đợi | `sync` |
| `VNP_TMN_CODE` | Mã website VNPay. **Để trống = dùng cổng giả lập** | (trống) |
| `VNP_HASH_SECRET` | Khóa bí mật VNPay | (trống) |
| `VNP_URL` | (tuỳ chọn) URL cổng VNPay | mặc định sandbox |
| `VNP_RETURN_URL` | (tuỳ chọn) URL nhận kết quả | mặc định `APP_URL/payment/vnpay/return` |

> ⚠️ **Không commit file `.env`** (đã được `.gitignore` chặn). Không dán khóa VNPay/DB thật vào bất kỳ đâu công khai. Phí ship và thời hạn thanh toán chỉnh trong `config/payment.php`.

## 9. Thanh toán VNPay

### 9.1 Dùng cổng giả lập (mặc định)
Để `VNP_TMN_CODE` trống. Khi chọn VNPay, hệ thống tạo giao dịch `gateway = mock` và chuyển tới `/payment/mock/{mã giao dịch}` với hai nút *Thành công* / *Thất bại*. Luồng ghi nhận giống hệt VNPay thật (đi qua cùng `PaymentService::settle()`).

### 9.2 Dùng VNPay Sandbox thật
1. Đăng ký tài khoản tại <https://sandbox.vnpayment.vn/>, lấy `TmnCode` và `HashSecret`.
2. Thêm vào `.env`:
   ```env
   VNP_TMN_CODE=<tmn-code-của-bạn>
   VNP_HASH_SECRET=<hash-secret-của-bạn>
   ```
3. Để VNPay gọi **IPN** về máy local, dùng ngrok: `ngrok http 8000`, rồi cấu hình trên cổng VNPay:
   `https://<tên-miền-ngrok>/payment/vnpay/ipn`
4. Thẻ test lấy từ tài liệu sandbox của VNPay.

### 9.3 Luồng xử lý
```
Đặt hàng ──► tạo Order + PaymentTransaction(pending) ──► redirect sang VNPay (URL có vnp_SecureHash)
                                                              │
        ┌─────────────────────────────────────────────────────┴──────────────┐
        ▼                                                                     ▼
 Return URL (trình duyệt khách)                                   IPN (server VNPay gọi)
        │  verifyVnpay() → settle()                                           │  verifyVnpay() → settle()
        └────────────────────► PaymentService::settle() ◄────────────────────┘
                 khóa dòng giao dịch · đối chiếu số tiền · nếu đã xử lý → "already" (bỏ qua)
```

## 10. Cấu trúc cơ sở dữ liệu

9 bảng nghiệp vụ (cộng `users`, `cache`, `jobs` mặc định của Laravel):

```
users ──< orders ──< order_items >── products >── categories ──< attributes
             │                          │  └──── brands            │
             └──< payment_transactions  └──< product_attribute_values >┘
```

| Bảng | Vai trò | Cột chính |
|---|---|---|
| `users` | Tài khoản | `role` (`customer`/`admin`), `phone` |
| `categories` | Danh mục | `name`, `slug` |
| `brands` | Thương hiệu | `name`, `slug` |
| `attributes` | **Định nghĩa thuộc tính theo danh mục** (EAV) | `category_id`, `code`, `type` (`text`/`number`/`select`), `options` (JSON), `is_required`, `is_filterable` |
| `products` | Sản phẩm | `sku`, `slug`, `price`, `sale_price`, `stock`, `status` (`draft`/`review`/`published`), `completeness`, xóa mềm |
| `product_attribute_values` | **Giá trị thuộc tính của từng sản phẩm** | `product_id`, `attribute_id`, `value` |
| `orders` | Đơn hàng | `code`, `subtotal`, `shipping_fee`, `total`, `payment_method`, `payment_status`, `status` |
| `order_items` | Dòng đơn (snapshot) | `name`, `sku`, `price`, `quantity` |
| `payment_transactions` | Giao dịch thanh toán | `gateway`, `transaction_code` (unique), `amount`, `status`, `raw_response` (JSON), `paid_at` |

**Vì sao thiết kế EAV (thuộc tính động)?** Tai nghe có "thời lượng pin", sạc có "công suất", mỗi loại sản phẩm cần bộ thông số khác nhau. Thay vì thêm cột cho từng loại, bảng `attributes` định nghĩa thông số theo danh mục, `product_attribute_values` lưu giá trị. Thêm một danh mục/thuộc tính mới **không cần sửa code hay migration**.

## 11. Cấu trúc thư mục

```
TechShop/
├── app/
│   ├── Console/Commands/CancelExpiredOrders.php   # lệnh hủy đơn quá hạn
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── ShopController.php                  # trang chủ, danh sách, chi tiết
│   │   │   ├── CartController.php                  # giỏ hàng
│   │   │   ├── CheckoutController.php              # đặt hàng
│   │   │   ├── PaymentController.php               # VNPay return/IPN + cổng giả lập
│   │   │   ├── AuthController.php                  # đăng ký/đăng nhập
│   │   │   ├── AccountController.php               # đơn hàng của tôi, tự hủy
│   │   │   └── Admin/                              # Dashboard, Product, Catalog, Order, ImportExport
│   │   └── Middleware/AdminOnly.php                # chặn người không phải admin
│   ├── Models/                                     # User, Category, Brand, Attribute, Product,
│   │                                               # ProductAttributeValue, Order, OrderItem, PaymentTransaction
│   └── Services/
│       ├── CartService.php                         # logic giỏ, tính phí ship
│       ├── OrderService.php                        # tạo đơn trong transaction + khóa dòng
│       └── PaymentService.php                      # ký/kiểm tra VNPay, settle idempotent
├── config/payment.php                              # cấu hình VNPay, phí ship, hạn thanh toán
├── database/
│   ├── migrations/                                 # schema
│   └── seeders/DatabaseSeeder.php                  # dữ liệu mẫu
├── resources/views/                                # Blade: shop, cart, checkout, payment, account, auth, admin
├── routes/
│   ├── web.php                                     # toàn bộ route
│   └── console.php                                 # lịch chạy orders:cancel-expired mỗi 5 phút
├── run.bat                                         # script chạy nhanh (Windows + Laragon)
└── README.md
```

## 12. Danh sách route

**Storefront**

| Method | URL | Chức năng |
|---|---|---|
| GET | `/` | Trang chủ |
| GET | `/san-pham` | Danh sách + lọc + tìm kiếm |
| GET | `/danh-muc/{slug}` | Sản phẩm theo danh mục |
| GET | `/san-pham/{slug}` | Chi tiết sản phẩm |
| GET/POST/PATCH/DELETE | `/gio-hang[/{id}]` | Xem, thêm, sửa, xóa giỏ |
| GET/POST | `/thanh-toan` | Form và đặt hàng (giới hạn 10 lần/phút) |
| GET | `/don-hang/{code}` | Trang kết quả đơn hàng |

**Thanh toán**

| Method | URL | Chức năng |
|---|---|---|
| GET | `/payment/vnpay/return` | VNPay chuyển khách về |
| GET | `/payment/vnpay/ipn` | VNPay gọi server-to-server |
| GET/POST | `/payment/mock/{code}` | Cổng giả lập |

**Tài khoản**

| Method | URL | Chức năng |
|---|---|---|
| GET/POST | `/dang-nhap`, `/dang-ky` | Đăng nhập, đăng ký (chỉ khi chưa đăng nhập) |
| POST | `/dang-xuat` | Đăng xuất |
| GET | `/tai-khoan/don-hang` | Lịch sử đơn |
| POST | `/tai-khoan/don-hang/{code}/huy` | Tự hủy đơn chưa trả tiền |

**Quản trị** (prefix `/admin`, cần đăng nhập + role admin)

| URL | Chức năng |
|---|---|
| `/admin` | Dashboard |
| `/admin/products` (resource) | CRUD sản phẩm |
| `/admin/catalog` | Danh mục, thương hiệu, thuộc tính |
| `/admin/orders`, `/admin/orders/{id}` | Danh sách, chi tiết, đổi trạng thái |
| `/admin/import-export`, `/admin/export`, `/admin/import` | CSV |

## 13. Các điểm kỹ thuật đáng chú ý

1. **Không bán vượt tồn kho.** `OrderService::place()` chạy trong `DB::transaction` và `lockForUpdate()` các dòng sản phẩm; kiểm tra tồn rồi mới trừ.
2. **Snapshot giá.** `order_items` lưu tên, SKU, giá tại thời điểm mua; đổi giá sau này không ảnh hưởng đơn cũ.
3. **Server tự tính tiền.** Tổng tiền và phí ship luôn tính lại từ DB, không tin dữ liệu gửi từ trình duyệt.
4. **State machine đơn hàng.** `pending → confirmed → shipping → completed`, có thể `cancelled` từ `pending`/`confirmed`. `completed` và `cancelled` là trạng thái cuối. Hủy sẽ hoàn kho.
5. **Thanh toán idempotent.** `PaymentService::settle()` khóa dòng giao dịch, đối chiếu số tiền; nếu IPN/Return gửi lặp thì trả `already`, không cộng tiền hai lần. Toàn bộ phản hồi cổng được lưu ở `raw_response`.
6. **Chữ ký VNPay.** Tạo và kiểm tra bằng HMAC-SHA512 trên các tham số `vnp_*` đã sắp xếp; so sánh bằng `hash_equals`.
7. **PIM động.** Form sản phẩm hiển thị ô thuộc tính theo danh mục; `Product::calculateCompleteness()` tính % hoàn thiện; không thể xuất bản nếu thiếu thuộc tính bắt buộc.
8. **Xóa mềm** sản phẩm (`SoftDeletes`) để không làm hỏng lịch sử đơn hàng.
9. **Phân quyền** bằng middleware `AdminOnly`; route đặt đơn có `throttle` chống spam.

## 14. Nhập/xuất CSV

Cột bắt buộc: `sku, name, category, price`. Đầy đủ:

```
sku,name,category,brand,price,stock,status,description,attributes
```

- `category` phải trùng **tên** danh mục đang có (VD `Tai nghe`).
- `status`: `draft` / `review` / `published` (sai → `draft`).
- `attributes`: các cặp `ma_thuoc_tinh=gia_tri` cách nhau bằng `;`. Mã thuộc tính xem ở Admin → Catalog (VD `ket_noi=Bluetooth 5.3;thoi_luong_pin_gio=40`).
- Thương hiệu chưa có sẽ được tạo tự động. SKU đã tồn tại thì **cập nhật**, chưa có thì **tạo mới**.
- File lưu UTF-8 (có BOM) để Excel đọc đúng tiếng Việt; tối đa 5 MB.
- Dòng lỗi được liệt kê kèm số dòng, các dòng hợp lệ vẫn được nhập.

Ví dụ:
```csv
sku,name,category,brand,price,stock,status,description,attributes
PK1001,Tai nghe Demo,Tai nghe,Sony,850000,20,published,Mô tả ngắn,ket_noi=Bluetooth 5.3;thoi_luong_pin_gio=30
```

## 15. Lệnh artisan hữu ích

| Lệnh | Tác dụng |
|---|---|
| `php artisan migrate:fresh --seed` | Xóa sạch DB, tạo lại bảng và dữ liệu mẫu (**mất toàn bộ dữ liệu**) |
| `php artisan db:seed` | Chỉ nạp dữ liệu mẫu |
| `php artisan orders:cancel-expired` | Hủy đơn VNPay quá 15 phút chưa thanh toán và hoàn kho |
| `php artisan schedule:work` | Chạy scheduler liên tục (mỗi 5 phút hủy đơn quá hạn) |
| `php artisan route:list` | Xem toàn bộ route |
| `php artisan storage:link` | Tạo liên kết `public/storage` cho ảnh upload |
| `php artisan optimize:clear` | Xóa cache cấu hình/view/route |

## 16. Xử lý sự cố thường gặp

| Triệu chứng | Nguyên nhân / cách xử lý |
|---|---|
| `Access denied for user 'root'` | Sai `DB_USERNAME`/`DB_PASSWORD`/`DB_PORT` trong `.env`. Kiểm tra MySQL đang chạy ở cổng nào. |
| `Unknown database 'techshop'` | Chưa tạo database (Bước 4). |
| `No application encryption key has been specified` | Chạy `php artisan key:generate`. |
| Sửa `.env` nhưng không có tác dụng | Chạy `php artisan optimize:clear`, khởi động lại `php artisan serve`. |
| Ảnh sản phẩm không hiện | Chạy `php artisan storage:link`. |
| Giao diện mất CSS | Máy không có Internet (Tailwind tải qua CDN). |
| `could not find driver` | Bật extension `pdo_mysql` trong `php.ini`. |
| `php`/`composer` không nhận lệnh | Thêm thư mục PHP/Composer vào biến môi trường PATH (Laragon: `C:\laragon\bin\php\...`). |
| Cổng 3306 bị chiếm | Đổi `DB_PORT` hoặc dừng dịch vụ MySQL còn lại. |
| Cổng 8000 bị chiếm | `php artisan serve --port=8080` và đổi `APP_URL`. |
| Tổng đơn VNPay báo sai số tiền | Số tiền VNPay trả về khác số tiền giao dịch; hệ thống từ chối (`wrong_amount`) để bảo vệ doanh thu. |

## 17. Phạm vi và hướng phát triển

**Có trong bản này:** ba trụ cột của đề tài như mô tả ở trên.

**Chủ động lược bỏ để giữ đơn giản** (có thể trình bày là hướng phát triển):
- Biến thể sản phẩm (SKU theo màu/dung lượng), đa kho
- Đa kênh bán
- Lưu lịch sử phiên bản dữ liệu sản phẩm (versioning)
- RBAC nhiều vai trò chi tiết
- Cổng thanh toán MoMo / ZaloPay, hoàn tiền tự động
- Đánh giá sản phẩm, wishlist, mã giảm giá
- Gửi email thông báo, hàng đợi (queue) nền

## 18. Giao diện

Web dùng tiếng Việt. Bấm công tắc có biểu tượng mặt trời/mặt trăng cạnh tài khoản ở đầu trang để đổi **Sáng / Tối** (trang quản trị: góc trên bên phải).

Chế độ sáng là mặc định. Lựa chọn được lưu trên trình duyệt và áp dụng cho cửa hàng lẫn quản trị khi tải lại hoặc chuyển trang. Nội dung sản phẩm hiển thị theo dữ liệu quản trị viên nhập.

Kiểm thử chức năng: `php artisan test --filter=PreferencesTest` (SQLite tạm trong bộ nhớ).

Danh sách sản phẩm mặc định có 6 sản phẩm/trang, có thể chọn 12 hoặc 24 ở **Mỗi trang**. Phân trang giữ từ khóa, bộ lọc và sắp xếp; trang vượt phạm vi được đưa về trang cuối. Kiểm thử: `php artisan test --filter=CatalogPaginationTest`.

---

© Dự án học phần **Lập trình mã nguồn mở**. Mã nguồn dùng cho mục đích học tập.
