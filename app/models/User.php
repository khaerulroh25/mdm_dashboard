<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Master catalog of granular permissions grouped by module.
     */
    public const PERMISSION_GROUPS = [
        'Manajemen Perangkat (Devices)' => [
            'devices.view' => [
                'label' => 'Lihat Daftar & Detail Perangkat',
                'desc'  => 'Membuka halaman daftar perangkat, detail spesifikasi, lokasi, dan telemetri.',
            ],
            'devices.sync' => [
                'label' => 'Sinkronisasi Google AMAPI',
                'desc'  => 'Menarik pembaruan status perangkat langsung dari Google Cloud AMAPI.',
            ],
            'devices.command' => [
                'label' => 'Eksekusi Remote Command & Ubah Policy',
                'desc'  => 'Mengirim perintah Lock, Reboot, Reset PIN, Mode Hilang, dan mengganti Policy perangkat.',
            ],
            'devices.edit' => [
                'label' => 'Edit Data Pemilik & Nama Model',
                'desc'  => 'Mengubah informasi kepemilikan perangkat (Owner) dan nama komersial model HP.',
            ],
            'devices.delete' => [
                'label' => 'Unenroll & Hapus Permanen Perangkat',
                'desc'  => 'Mencabut pengelolaan perangkat (Unenroll) dan menghapus arsip dari database.',
            ],
        ],
        'Kebijakan Keamanan (Policies)' => [
            'policies.view' => [
                'label' => 'Lihat Daftar Kebijakan',
                'desc'  => 'Melihat daftar policy yang tersedia beserta konfigurasi pembatasannya.',
            ],
            'policies.manage' => [
                'label' => 'Kelola Kebijakan (Buat, Edit, Hapus)',
                'desc'  => 'Membuat policy baru, mengubah pembatasan sistem, mengatur aplikasi wajib, dan menghapus policy.',
            ],
        ],
        'Katalog Aplikasi (Google Play)' => [
            'apps.view' => [
                'label' => 'Lihat Katalog Aplikasi',
                'desc'  => 'Melihat daftar aplikasi Android yang tersimpan di katalog MDM.',
            ],
            'apps.create' => [
                'label' => 'Tambah Aplikasi ke Katalog',
                'desc'  => 'Mencari dan menambahkan aplikasi dari Google Play Store atau secara manual.',
            ],
            'apps.delete' => [
                'label' => 'Hapus Aplikasi dari Katalog',
                'desc'  => 'Menghapus aplikasi yang tersimpan dari katalog MDM.',
            ],
        ],
        'Pendaftaran & Pengawasan Sistem' => [
            'enrollment.manage' => [
                'label' => 'Buat & Cetak QR Code Enrollment',
                'desc'  => 'Mengakses menu Enrollment dan men-generate token/QR pendaftaran HP baru.',
            ],
            'audit_logs.view' => [
                'label' => 'Lihat Riwayat Audit Logs',
                'desc'  => 'Memantau rekaman jejak aktivitas pengguna dan riwayat perintah sistem.',
            ],
            'users.manage' => [
                'label' => 'Kelola Akun & Permission Pengguna',
                'desc'  => 'Menambah, mengubah profil/hak akses, dan menghapus akun pengguna dashboard.',
            ],
        ],
    ];

    /**
     * Default permission presets for each role.
     */
    public const ROLE_PRESETS = [
        'admin' => [
            'devices.view',
            'devices.sync',
            'devices.command',
            'devices.edit',
            'devices.delete',
            'policies.view',
            'policies.manage',
            'apps.view',
            'apps.create',
            'apps.delete',
            'enrollment.manage',
            'audit_logs.view',
            'users.manage',
        ],
        'operator' => [
            'devices.view',
            'devices.sync',
            'devices.command',
            'devices.edit',
            'policies.view',
            'apps.view',
            'apps.create',
            'enrollment.manage',
        ],
        'auditor' => [
            'devices.view',
            'policies.view',
            'apps.view',
            'audit_logs.view',
        ],
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'name',
        'email',
        'password',
        'role',
        'permissions',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'permissions' => 'array',
        ];
    }

    /**
     * Get all valid permission keys as a flat list.
     */
    public static function allPermissionKeys(): array
    {
        $keys = [];
        foreach (self::PERMISSION_GROUPS as $group) {
            foreach ($group as $key => $meta) {
                $keys[] = $key;
            }
        }
        return $keys;
    }

    /**
     * Get default permissions for a given role.
     */
    public static function defaultPermissionsForRole(string $role): array
    {
        if ($role === 'admin') {
            return self::allPermissionKeys();
        }

        return self::ROLE_PRESETS[$role] ?? [];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isOperator(): bool
    {
        return $this->role === 'operator';
    }

    public function isAuditor(): bool
    {
        return $this->role === 'auditor';
    }

    public function hasRole(string|array $roles): bool
    {
        if (is_array($roles)) {
            return in_array($this->role, $roles);
        }
        return $this->role === $roles;
    }

    /**
     * Get the effective list of permissions granted to this user.
     * Superadmin ('admin') always receives all permissions.
     */
    public function getEffectivePermissions(): array
    {
        if ($this->isAdmin()) {
            return self::allPermissionKeys();
        }

        if (is_array($this->permissions)) {
            return array_values(array_intersect($this->permissions, self::allPermissionKeys()));
        }

        return self::defaultPermissionsForRole($this->role);
    }

    /**
     * Check if the user has the given permission(s).
     * Superadmin ('admin') automatically has all permissions.
     */
    public function hasPermission(string|array $permissions): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $effective = $this->getEffectivePermissions();

        if (is_array($permissions)) {
            foreach ($permissions as $perm) {
                if (in_array($perm, $effective, true)) {
                    return true;
                }
            }
            return false;
        }

        return in_array($permissions, $effective, true);
    }

    public function deviceCommands(): HasMany
    {
        return $this->hasMany(DeviceCommand::class, 'issued_by_user_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'user_id');
    }
}
