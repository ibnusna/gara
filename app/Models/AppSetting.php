<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $connection = 'mysql_apps';
    protected $table = 'app_settings';

    protected $primaryKey = 'setting_key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['setting_key', 'setting_value'];
    public $timestamps = false;

    public static function getLogo($defaultFile, $forceDefault = false)
    {
        if ($forceDefault) {
            return asset('logo/' . $defaultFile);
        }

        $mode = \Illuminate\Support\Facades\Cache::rememberForever('logo_mode', function () {
            return self::where('setting_key', 'logo_mode')->value('setting_value') ?? 'default';
        });

        if ($mode === 'custom') {
            $customFilename = \Illuminate\Support\Facades\Cache::rememberForever('custom_logo_filename', function () {
                return self::where('setting_key', 'custom_logo_filename')->value('setting_value') ?? 'logo_sekolah.png';
            });
            return asset('logo/' . $customFilename);
        } elseif ($mode === 'preset_tutwuri') {
            return asset('logo/logo_tutwuri.svg');
        }

        return asset('logo/' . $defaultFile);
    }
}
