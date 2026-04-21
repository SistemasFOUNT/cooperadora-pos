<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable implements Auditable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, AuditableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'branch_id',
        'punto_venta_id',
        'role',
        'permisos',
        'employee_number',
        'status',
        'preferences',
    ];

    // Eliminar getAuthIdentifierName para que use el ID por defecto

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
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
            'preferences' => 'array',
            'permisos' => 'array',
        ];
    }

    /**
     * Relación con punto de venta
     */
    public function puntoVenta(): BelongsTo
    {
        return $this->belongsTo(PuntoVenta::class, 'punto_venta_id');
    }

    /**
     * Relación con sucursal
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Relación con ventas
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Relación con movimientos de caja
     */
    public function cashMovements(): HasMany
    {
        return $this->hasMany(CashMovement::class);
    }

    /**
     * Verifica si el usuario es administrador
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Verifica si el usuario puede acceder a un punto de venta específico
     */
    public function canAccessPuntoVenta($puntoVentaId): bool
    {
        if ($this->isAdmin()) {
            return true; // Admin puede ver todo
        }

        return $this->punto_venta_id == $puntoVentaId;
    }

    /**
     * Obtiene el nombre del rol en español
     */
    public function getRoleNameAttribute(): string
    {
        $roles = [
            'admin' => 'Administrador',
            'usuario_box' => 'Usuario BOX Cooperadora',
            'usuario_postgrado' => 'Usuario Postgrado',
            'usuario_odonto' => 'Usuario Centro Odontológico'
        ];

        return $roles[$this->role] ?? 'Usuario';
    }

    /**
     * Scope para filtrar usuarios por punto de venta
     */
    public function scopeByPuntoVenta($query, $puntoVentaId)
    {
        return $query->where('punto_venta_id', $puntoVentaId);
    }

    /**
     * Scope para usuarios de un punto de venta específico
     */
    public function scopeBoxUsers($query)
    {
        return $query->where('role', 'usuario_box');
    }

    public function scopePostgradoUsers($query)
    {
        return $query->where('role', 'usuario_postgrado');
    }

    public function scopeOdontoUsers($query)
    {
        return $query->where('role', 'usuario_odonto');
    }

    public function scopeAdminUsers($query)
    {
        return $query->where('role', 'admin');
    }

    /**
     * Métodos requeridos por AdminLTE
     */

    /**
     * Obtiene la URL del perfil del usuario para AdminLTE
     */
    public function adminlte_profile_url()
    {
        if ($this->isAdmin()) {
            return route('admin.profile'); // Ruta específica para admin
        }

        return route('profile.edit'); // Ruta general para otros usuarios
    }

    /**
     * Obtiene la imagen del usuario para AdminLTE
     */
    public function adminlte_image()
    {
        // Retornar false para deshabilitar completamente la imagen
        return false;
    }

    /**
     * Obtiene la descripción del usuario para AdminLTE
     */
    public function adminlte_desc()
    {
        if ($this->isAdmin()) {
            return 'Superusuario - Acceso completo';
        }

        return $this->getRoleNameAttribute();
    }
}
