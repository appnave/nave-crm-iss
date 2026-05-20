<?php

namespace Bildvitta\IssCrm\Models\Customer;

use Bildvitta\IssCrm\Models\DocumentType;
use Bildvitta\IssCrm\Traits\UsesCrmDB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

class Document extends Model
{
    use SoftDeletes;
    use UsesCrmDB;

    protected $connection = 'iss-crm';

    protected $table = 'customer_documents';

    protected $guard_name = 'web';

    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $model->uuid = (string) Uuid::uuid4();
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function document_type()
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id', 'id')->withTrashed();
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id')->withoutGlobalScopes()->withTrashed();
    }

    public function getFileAttribute($value)
    {
        if (! filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        $fileUrl = parse_url($value);
        $fileHost = $fileUrl['host'];
        $fileFullPath = $fileUrl['path'];
        $filePath = substr($fileFullPath, 1);

        $fileName = explode('/', $fileFullPath);
        $fileName = end($fileName);

        if (! Str::contains($fileHost, '.amazonaws.com')) {
            return $value;
        }

        $disk = 's3';

        if (config('filesystems.disks.s3_crm.key') && Str::contains($fileHost, 'pdaw-crmap01-assets.s3.amazonaws.com')) {
            $disk = 's3_crm';
        }

        if (config('filesystems.disks.s3_sys.key') && (Str::contains($fileHost, 's3-bild-sys.s3.amazonaws.com') || Str::contains($fileHost, 'sys-prod-app-bkp.s3'))) {
            $disk = 's3_sys';
        }

        return Storage::disk($disk)->temporaryUrl(
            $filePath,
            now()->addDays(7),
            [
                'ResponseContentDisposition' => 'inline; filename="'.$fileName.'"',
                'ResponseContentType' => Storage::disk($disk)->mimeType($filePath),
            ]
        );
    }
}
