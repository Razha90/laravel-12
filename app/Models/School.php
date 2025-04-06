<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;

    protected $table = 'sekolah';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'kode_prop', 'propinsi', 'kode_kab_kota', 'kabupaten_kota', 'kode_kec', 'kecamatan', 
        'id', 'npsn', 'sekolah', 'bentuk', 'status', 'alamat_jalan', 'lintang', 'bujur'
    ];
}
