<?php

namespace admin\enquiries\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Kyslik\ColumnSortable\Sortable;
use admin\admin_auth\Models\Admin;
use Illuminate\Support\Facades\Config;

class Enquiry extends Model
{
    use HasFactory, Sortable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'name',
        'email',
        'message',
        'admin_reply',
        'status',
        'is_replied',
        'replied_at',
        'replied_by',
    ];

    protected $casts = [
        'is_replied' => 'boolean',
        'replied_at' => 'datetime',
    ];

    /**
     * The attributes that should be sortable.
     */
    public $sortable = [
        'name',
        'email',
        'message',
        'admin_reply',
        'status',
        'created_at'
    ];

    /**
     * Replying Admin Relationship
     */
    public function repliedBy()
    {
        return $this->belongsTo(Admin::class, 'replied_by');
    }

    /**
     * Enum-like accessor for readable status (optional)
     */
    public function getStatusLabelAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }

    public function scopeFilter($query, $keyword)
    {
        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%')
                    ->orWhere('email', 'like', '%' . $keyword . '%');
            });
        }
        return $query;
    }

    public function scopeFilterByStatus($query, $status)
    {
        if (!is_null($status) && $status !== '') {
            return $query->where('status', $status);
        }
        return $query;
    }

    public static function getPerPageLimit(): int
    {
        return Config::has('get.admin_page_limit')
            ? Config::get('get.admin_page_limit')
            : 10;
    }
}
