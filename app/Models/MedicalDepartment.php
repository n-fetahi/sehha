<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicalDepartment extends Model
{
    protected $fillable = ['name'];

    public function clinics()
    {
        return $this->hasMany(Clinic::class, 'medical_department_id');
    }
}



