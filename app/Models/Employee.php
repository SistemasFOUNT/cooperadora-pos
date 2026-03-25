<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Employee extends Model implements Auditable
{
    use HasFactory, AuditableTrait;

    protected $fillable = [
        'employee_number',
        'first_name',
        'last_name',
        'document_type',
        'document_number',
        'cuil',
        'birth_date',
        'phone',
        'email',
        'address',
        'hire_date',
        'termination_date',
        'status',
        'base_salary',
        'additional_data',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'hire_date' => 'date',
        'termination_date' => 'date',
        'base_salary' => 'decimal:2',
        'additional_data' => 'array',
    ];

    /**
     * Scope para empleados activos
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Obtener nombre completo
     */
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
