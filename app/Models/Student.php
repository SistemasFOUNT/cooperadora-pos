<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model implements Auditable
{
    use HasFactory, AuditableTrait;

    protected $fillable = [
        'student_number',
        'first_name',
        'last_name',
        'document_type',
        'document_number',
        'phone',
        'email',
        'address',
        'career_type',
        'career_name',
        'academic_year',
        'fee_frequency',
        'fee_amount',
        'enrollment_date',
        'status',
        'additional_data',
    ];

    protected $casts = [
        'enrollment_date' => 'date',
        'fee_amount' => 'decimal:2',
        'academic_year' => 'integer',
        'additional_data' => 'array',
    ];

    /**
     * Relación con ventas (pagos de cuotas)
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Scope para estudiantes activos
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
