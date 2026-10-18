# System Design Document (SDD)
## MDM Dashboard Architecture & Laravel Migration Blueprint

- **Document Version:** 1.0.0
- **Status:** Approved for Implementation
- **Tech Stack:** Laravel 11.x, PHP 8.2+, MySQL 8.0 / MariaDB 10.6, Google Cloud AMAPI (v1)
- **Author:** Senior Software Engineer

---

## 1. High-Level System Architecture

Sistem MDM Dashboard dirancang menggunakan pendekatan **Clean Layered Architecture (Multi-Tier)** dengan pemisahan tegas antara antarmuka (Presentation), logika aplikasi (HTTP/Controllers), logika bisnis inti (Domain/Service Layer), penyimpanan persisten (Database/Eloquent), dan integrasi pihak ketiga (Google Cloud Platform).

```mermaid
flowchart TD
    subgraph Presentation_Layer["1. Presentation Layer"]
        UI["Web Dashboard (Blade + Bootstrap 5 / DataTables)"]
        QR_VIEW["Enrollment QR Interface"]
    end

    subgraph HTTP_Security_Layer["2. HTTP & Security Layer"]
        ROUTER["Laravel Routing (routes/web.php)"]
        AUTH_MID["Auth & Role Middleware (admin/operator)"]
        CSRF["CSRF Protection & Rate Limiter"]
        REQ_VAL["Form Request Validation"]
    end

    subgraph Controller_Layer["3. Controller Layer (Skinny)"]
        DEV_CTRL["DeviceController"]
        POL_CTRL["PolicyController"]
        APP_CTRL["GooglePlayController"]
        ENR_CTRL["EnrollmentController"]
        OWN_CTRL["DeviceOwnerController"]
    end

    subgraph Service_Layer["4. Service & Domain Layer"]
        AMAPI_SVC["AndroidManagementService"]
        PLAY_SVC["GooglePlayService"]
        QR_SVC["QrCodeService"]
        SYNC_SVC["DeviceSyncService"]
    end

    subgraph Async_Worker_Layer["5. Asynchronous Queue & Jobs"]
        QUEUE["Laravel Queue (Database / Redis)"]
        CMD_JOB["IssueDeviceCommandJob"]
        SYNC_JOB["SyncDevicesJob"]
    end

    subgraph Persistence_Layer["6. Persistence & Storage"]
        DB[(MySQL 8.0 Database)]
        AUDIT_LOGS[("audit_logs & device_commands")]
        CACHE[("Cache Driver (Redis/File)")]
    end

    subgraph External_Integrations["7. External Ecosystem"]
        GCP_AMAPI["Google Android Management API (v1)"]
        GPLAY["Google Play Store (Nelexa Scraper)"]
        DEVICES["Managed Android Devices (Field)"]
    end

    UI --> ROUTER
    QR_VIEW --> ROUTER
    ROUTER --> AUTH_MID --> CSRF --> REQ_VAL
    REQ_VAL --> DEV_CTRL & POL_CTRL & APP_CTRL & ENR_CTRL & OWN_CTRL

    DEV_CTRL --> AMAPI_SVC
    DEV_CTRL --> QUEUE
    POL_CTRL --> AMAPI_SVC
    APP_CTRL --> PLAY_SVC
    ENR_CTRL --> AMAPI_SVC & QR_SVC
    OWN_CTRL --> DB

    QUEUE --> CMD_JOB & SYNC_JOB
    CMD_JOB --> AMAPI_SVC
    SYNC_JOB --> AMAPI_SVC

    AMAPI_SVC --> GCP_AMAPI
    PLAY_SVC --> GPLAY
    AMAPI_SVC --> DB
    SYNC_SVC --> DB

    GCP_AMAPI -. Push Policy / Command .-> DEVICES
    DEVICES -. Enroll with Token .-> GCP_AMAPI
```

---

## 2. Technology Stack & Libraries

| Komponen | Pilihan Teknologi | Alasan & Manfaat Teknis |
| :--- | :--- | :--- |
| **Framework Utama** | **Laravel 11.x** (PHP 8.2+) | Standar industri, Dependency Injection kelas satu, ekosistem keamanan matang, Eloquent ORM. |
| **Database** | **MySQL 8.0 / MariaDB 10.6+** | Relasional ACID, performa tinggi dengan indexing JSON data (untuk menyimpan payload AMAPI). |
| **Queue & Worker** | **Laravel Queue (Database / Redis)** | Mengubah eksekusi perintah sinkron yang lambat menjadi proses asinkron instan. |
| **Google API Client** | `google/apiclient: ^2.18` | Library resmi Google untuk otentikasi Service Account OAuth2 dan interaksi AMAPI. |
| **QR Code Engine** | `endroid/qr-code: ^5.0` | Pembuatan QR enrollment token langsung dalam bentuk Base64 Data URI untuk tag `<img>`. |
| **Play Store Engine** | `nelexa/google-play-scraper: ^0.5` | Scraping metadata aplikasi Android resmi (ikon, package name, judul). |
| **Frontend Framework** | **Blade + Bootstrap 5 + DataTables** | Ringan, kompatibel dengan layout aset lama, cepat diimplementasikan tanpa overhead SPA build kompleks. |

---

## 3. Database Architecture & ERD

Database dirancang untuk menjadi **Single Source of Truth** lokal agar performa dashboard tidak lagi bergantung pada panggilan HTTP berulang ke Google Cloud.

```mermaid
erDiagram
    users ||--o{ audit_logs : "creates"
    users ||--o{ device_commands : "issues"
    policies ||--o{ devices : "applies_to"
    policies ||--o{ policy_applications : "contains"
    apps ||--o{ policy_applications : "assigned_in"
    devices ||--o| device_owners : "assigned_to"
    devices ||--o{ device_commands : "receives"

    users {
        bigint id PK
        string username UK
        string name
        string email
        string password
        enum role "admin, operator, auditor"
        timestamp created_at
    }

    devices {
        bigint id PK
        string device_id UK "Resource name AMAPI"
        string hardware_model
        string brand
        string manufacturer
        string serial_number
        string android_version
        string state "ACTIVE, PROVISIONING, DISABLED"
        string policy_id FK
        json network_info
        json memory_info
        timestamp last_status_report
        timestamp enrolled_at
        timestamp updated_at
    }

    device_owners {
        bigint id PK
        bigint device_id FK UK
        string nama
        string id_anggota "Unique member ID"
        string telepon
        string departemen
        text catatan
        timestamp created_at
    }

    policies {
        bigint id PK
        string policy_id UK "AMAPI identifier (e.g. default, kiosk)"
        string name "Display name"
        text description
        boolean camera_disabled
        boolean wifi_config_disabled
        boolean factory_reset_disabled
        boolean install_apps_disabled
        boolean safe_boot_disabled
        string location_mode
        json raw_policy_json
        timestamp updated_at
    }

    apps {
        bigint id PK
        string package_name UK
        string app_name
        string icon_url
        string developer
        timestamp created_at
    }

    policy_applications {
        bigint id PK
        bigint policy_id FK
        bigint app_id FK
        enum install_type "FORCE_INSTALLED, AVAILABLE, KIOSK"
        boolean auto_update
    }

    device_commands {
        bigint id PK
        bigint device_id FK
        bigint issued_by_user_id FK
        string command_type "LOCK, REBOOT, RESET_PASSWORD, START_LOST_MODE, STOP_LOST_MODE"
        json command_params
        enum status "PENDING, SUCCESS, FAILED"
        string amapi_command_id
        text error_message
        timestamp executed_at
    }

    audit_logs {
        bigint id PK
        bigint user_id FK
        string action
        string target_type
        string target_id
        json old_values
        json new_values
        string ip_address
        string user_agent
        timestamp created_at
    }
```

---

## 4. Class Design & Service Layer Pattern

Semua interaksi pihak ketiga diisolasi ke dalam **Services** dengan prinsip *Single Responsibility Principle* (SRP) dan *Dependency Injection*.

### 4.1 `AndroidManagementService`
Bertanggung jawab menangani seluruh komunikasi ke Google Android Management API via `Google\Client`.

```php
namespace App\Services;

use Google\Client;
use Google\Service\AndroidManagement;
use Google\Service\AndroidManagement\Command;
use Google\Service\AndroidManagement\Policy;
use Google\Service\AndroidManagement\EnrollmentToken;

class AndroidManagementService
{
    protected AndroidManagement $service;
    protected string $enterpriseId;

    public function __construct()
    {
        $client = new Client();
        $client->setAuthConfig(config('amapi.credentials_path'));
        $client->addScope(AndroidManagement::ANDROIDMANAGEMENT);

        $this->service = new AndroidManagement($client);
        $this->enterpriseId = config('amapi.enterprise_id');
    }

    // Devices
    public function listDevices(int $pageSize = 100, ?string $pageToken = null);
    public function getDevice(string $deviceId);
    public function unenrollDevice(string $deviceId): bool;
    public function patchDevicePolicy(string $deviceId, string $policyId);

    // Commands
    public function issueCommand(string $deviceId, string $type, array $params = []): Command;

    // Policies
    public function listPolicies();
    public function getPolicy(string $policyId): Policy;
    public function patchPolicy(string $policyId, array $policyData): Policy;
    public function deletePolicy(string $policyId): bool;

    // Enrollment
    public function createEnrollmentToken(string $policyId, int $durationSeconds = 604800): EnrollmentToken;
}
```

### 4.2 `GooglePlayService`
Bertanggung jawab melakukan scraping data aplikasi dari Play Store dan melakukan fuzzy sorting berdasarkan relevansi pencarian.

```php
namespace App\Services;

use Nelexa\GPlay\GPlayApps;
use Illuminate\Support\Facades\Cache;

class GooglePlayService
{
    protected GPlayApps $gplay;

    public function __construct()
    {
        $this->gplay = new GPlayApps();
    }

    public function search(string $keyword, int $limit = 30): array
    {
        $cacheKey = 'gplay_search_' . md5(strtolower($keyword));

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($keyword, $limit) {
            $results = $this->gplay->search($keyword, 100);
            $apps = [];
            foreach ($results as $app) {
                if ($app) {
                    $apps[] = [
                        'app_name'     => $app->getName() ?? 'Unknown',
                        'package_name' => $app->getId() ?? '',
                        'icon'         => $app->getIcon() ? $app->getIcon()->getUrl() : null,
                        'developer'    => $app->getDeveloper() ? $app->getDeveloper()->getName() : 'Unknown',
                    ];
                }
            }
            return array_slice($apps, 0, $limit);
        });
    }
}
```

### 4.3 `QrCodeService`
Menghasilkan QR code token pendaftaran perangkat dalam format Base64 Data URI untuk ditampilkan di peramban.

```php
namespace App\Services;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

class QrCodeService
{
    public function generateBase64Qr(string $payload): string
    {
        $result = Builder::create()
            ->writer(new PngWriter())
            ->data($payload)
            ->size(280)
            ->margin(12)
            ->build();

        return $result->getDataUri();
    }
}
```

---

## 5. Sequence Diagrams

### 5.1 Remote Command Execution (Lock / Reset PIN / Reboot)
Memastikan eksekusi perintah berlangsung asinkron sehingga pengguna tidak mengalami *browser hanging*.

```mermaid
sequenceDiagram
    autonumber
    actor Admin as Admin / Operator
    participant UI as Dashboard View
    participant Ctrl as DeviceController
    participant Queue as Laravel Queue
    participant Worker as IssueDeviceCommandJob
    participant Svc as AndroidManagementService
    participant AMAPI as Google Cloud AMAPI
    participant DB as MySQL Database

    Admin->>UI: Klik "Lock Device" / "Reset PIN"
    UI->>Ctrl: POST /devices/{id}/command (with CSRF & Payload)
    Ctrl->>DB: Catat DeviceCommand (Status: PENDING)
    Ctrl->>Queue: Dispatch IssueDeviceCommandJob(commandId)
    Ctrl-->>UI: 302 Redirect with Flash: "Perintah dijadwalkan!"
    UI-->>Admin: Notifikasi Sukses Instan (< 250ms)

    Note over Queue,Worker: Background Queue Processing
    Queue->>Worker: Handle Job
    Worker->>Svc: issueCommand(deviceId, type, params)
    Svc->>AMAPI: POST enterprises/{id}/devices/{id}:issueCommand
    AMAPI-->>Svc: Command Response (amapi_command_id)
    Worker->>DB: Update DeviceCommand (Status: SUCCESS, amapi_command_id)
    Worker->>DB: Insert AuditLog (Action: DEVICE_LOCKED, User: Admin)
```

### 5.2 Device Enrollment Flow
Alur pendaftaran perangkat baru dari pabrik (*Out of the Box*) menggunakan token QR.

```mermaid
sequenceDiagram
    autonumber
    actor Admin as Admin / Operator
    participant UI as Enrollment View
    participant Ctrl as EnrollmentController
    participant Svc as AndroidManagementService
    participant QR as QrCodeService
    participant AMAPI as Google Cloud AMAPI
    actor Android as Managed Android Phone

    Admin->>UI: Buka form Enrollment, pilih Policy
    Admin->>UI: Submit "Generate QR Code"
    UI->>Ctrl: POST /enrollment/token (policyId)
    Ctrl->>Svc: createEnrollmentToken(policyId)
    Svc->>AMAPI: POST enterprises/{id}/enrollmentTokens
    AMAPI-->>Svc: Token Payload & Expiration
    Ctrl->>QR: generateBase64Qr(tokenValue)
    QR-->>Ctrl: Data URI Image
    Ctrl-->>UI: Render Halaman QR Code
    Admin->>Android: Ketuk layar 6x di Welcome Screen
    Android->>UI: Scan QR Code
    Android->>AMAPI: Kontak AMAPI & Terapkan Policy
    AMAPI-->>Android: Unduh Android Device Policy App
```

### 5.3 Scheduled Device Fleet Synchronization
Menjaga keselarasan data armada antara Google Cloud dan database lokal secara otomatis.

```mermaid
sequenceDiagram
    autonumber
    participant Cron as Laravel Scheduler (CRON)
    participant Cmd as php artisan mdm:sync-devices
    participant Svc as AndroidManagementService
    participant AMAPI as Google Cloud AMAPI
    participant DB as MySQL Database

    Cron->>Cmd: Jalankan tiap 15 menit
    Cmd->>Svc: listDevices()
    Svc->>AMAPI: GET enterprises/{id}/devices
    AMAPI-->>Svc: List of Device Objects
    loop Setiap Device dari Google
        Cmd->>DB: Upsert ke tabel `devices` (Model, State, Network, Battery)
    end
    Cmd-->>Cron: Sinkronisasi Selesai (Logged)
```

---

## 6. Route Specifications (Laravel Route Map)

### 6.1 Web Routes (`routes/web.php`)
Semua rute web dienkapsulasi dengan middleware `web` (Session, CSRF) dan `auth`.

| HTTP Method | URI | Controller & Action | Hak Akses | Keterangan |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/dashboard` | `DashboardController@index` | Semua | Ringkasan metrik armada & statistik. |
| `GET` | `/devices` | `DeviceController@index` | Semua | Daftar perangkat (search, filter). |
| `GET` | `/devices/{device}` | `DeviceController@show` | Semua | Detail perangkat, status, hardware info. |
| `POST` | `/devices/{device}/command` | `DeviceController@issueCommand` | Admin, Operator | Kirim Lock, Reboot, Reset PIN, Lost Mode. |
| `POST` | `/devices/{device}/policy` | `DeviceController@updatePolicy` | Admin, Operator | Ubah policy aktif perangkat. |
| `DELETE`| `/devices/{device}/unenroll` | `DeviceController@unenroll` | Admin | Hapus dan reset perangkat secara remote. |
| `POST` | `/devices/sync` | `DeviceController@sync` | Admin, Operator | Trigger manual sinkronisasi dari Google. |
| `GET` | `/owners` | `DeviceOwnerController@index` | Semua | Daftar penugasan pemilik perangkat. |
| `POST` | `/owners` | `DeviceOwnerController@store` | Admin, Operator | Tautkan data pemilik ke perangkat. |
| `PUT` | `/owners/{id}` | `DeviceOwnerController@update` | Admin, Operator | Perbarui data pemilik/anggota. |
| `GET` | `/policies` | `PolicyController@index` | Semua | Daftar policy AMAPI. |
| `GET` | `/policies/create` | `PolicyController@create` | Admin | Tampilan form pembuat policy baru. |
| `POST` | `/policies` | `PolicyController@store` | Admin | Simpan & kirim policy baru ke AMAPI. |
| `GET` | `/policies/{id}/edit` | `PolicyController@edit` | Admin | Form edit konfigurasi policy. |
| `PUT` | `/policies/{id}` | `PolicyController@update` | Admin | Patch konfigurasi policy ke AMAPI. |
| `DELETE`| `/policies/{id}` | `PolicyController@destroy` | Admin | Hapus policy di Google AMAPI. |
| `GET` | `/apps` | `GooglePlayController@index` | Semua | Katalog aplikasi lokal. |
| `GET` | `/apps/search` | `GooglePlayController@search` | Admin, Operator | Cari aplikasi di Google Play Store. |
| `POST` | `/apps` | `GooglePlayController@store` | Admin | Simpan aplikasi ke database katalog. |
| `POST` | `/policies/{id}/apps` | `PolicyController@addApp` | Admin | Pasang aplikasi ke policy (*FORCE_INSTALLED*). |
| `GET` | `/enrollment` | `EnrollmentController@index` | Admin, Operator | Form pemilihan policy enrollment. |
| `POST` | `/enrollment/token` | `EnrollmentController@generate`| Admin, Operator | Buat token dan tampilkan QR code. |
| `GET` | `/audit-logs` | `AuditLogController@index` | Admin, Auditor | Riwayat log eksekusi dan aktivitas sistem. |
| `RESOURCE`| `/users` | `UserController` | Admin | Manajemen pengguna dashboard. |

---

## 7. Security & Secret Management

```mermaid
flowchart LR
    subgraph Env_Management["Manajemen Kredensial (.env)"]
        ENV[".env (TIDAK di-commit ke Git)"]
        KEY["GOOGLE_APPLICATION_CREDENTIALS=storage/app/credentials/sa.json"]
        ENT["AMAPI_ENTERPRISE_ID=enterprises/LC041be11h"]
    end

    subgraph Storage_Security["Isolasi File Private"]
        SA_FILE["storage/app/credentials/sa.json (Strict Permission 600)"]
    end

    subgraph Defense_In_Depth["Layer Keamanan Aplikasi"]
        CSRF_TOK["CSRF Token pada setiap form"]
        THROTTLE["Rate Limiting: 60 req/min untuk Command"]
        AUDIT["Audit Trail 100% pada tabel audit_logs"]
    end

    ENV --> KEY & ENT
    KEY --> SA_FILE
```

1. **Zero Secret Leakage:**
   - Hapus `ServiceAccount.json` dari folder publik/config repo.
   - Pindahkan ke `storage/app/credentials/google-service-account.json`.
   - Pastikan path ini terdaftar di `.gitignore` dan permissions diatur ke `chmod 600`.
2. **CSRF & Session Hijacking Protection:**
   - Semua formulir menggunakan `@csrf` Blade directive.
   - Session cookie diatur dengan flag `HttpOnly`, `SameSite=Lax`, dan `Secure` (di lingkungan HTTPS).
3. **Double Confirmation for High-Risk Actions:**
   - Perintah `Factory Reset / Unenroll` dan `Wipe` mewajibkan modal konfirmasi dengan pengetikan kata kunci (misal: `"CONFIRM"`) sebelum form dapat disubmit.

---

## 8. Deployment Architecture & Production Setup

```mermaid
flowchart TD
    subgraph Host_Server["Linux Server (Ubuntu 22.04 / 24.04 LTS)"]
        NGINX["Nginx Web Server (Reverse Proxy & SSL)"]
        PHP_FPM["PHP 8.2+ FPM (Document Root: /var/www/mdm/public)"]
        CRON["Linux Cron (php artisan schedule:run)"]
        SUPERVISOR["Supervisor Daemon Manager"]
        WORKER["php artisan queue:work (Background Queue Worker)"]
        MYSQL[("MySQL 8.0 Server (Localhost)")]
    end

    NGINX -->|FastCGI| PHP_FPM
    SUPERVISOR -->|Manage & Restart| WORKER
    CRON -->|Every Minute| PHP_FPM
    PHP_FPM --> MYSQL
    WORKER --> MYSQL
```

### Rekomendasi Konfigurasi Supervisor (`/etc/supervisor/conf.d/mdm-worker.conf`):
```ini
[program:mdm-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/mdm/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/mdm/storage/logs/worker.log
stopwaitsecs=3600
```
