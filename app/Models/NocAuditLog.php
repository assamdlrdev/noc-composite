<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NocAuditLog extends Model
{
    protected $table = 'noc_audit_log_table';

    protected $primaryKey = 'slno';

    public $timestamps = false;

    protected $guarded = [];
}