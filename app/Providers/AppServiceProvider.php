<?php

namespace App\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use App\Models\SystemSetting;
use Illuminate\Database\QueryException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        try {
            // Attempt to load all system settings into config
            $settings = SystemSetting::all();

            // Loop through all the settings and dynamically set the configuration values
            foreach ($settings as $setting) {
                // Set S3-related configuration values
                if (in_array($setting->key, [
                    'AWS_ACCESS_KEY_ID',
                    'AWS_SECRET_ACCESS_KEY',
                    'AWS_DEFAULT_REGION',
                    'AWS_BUCKET',
                    'AWS_URL',
                    'AWS_ENDPOINT',
                    'AWS_USE_PATH_STYLE_ENDPOINT',
                ])) {
                    Config::set('filesystems.disks.s3.' . $setting->key, $setting->value);
                }else{
                    Config::set($setting->key, $setting->value);
                }

                // Optionally, you can set them as environment variables (for env overrides)
                $_ENV[$setting->key] = $setting->value;
            }

        } catch (QueryException $e) {
            // Log the error but continue running the application
            \Log::error('Error loading system settings: ' . $e->getMessage());
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Register any application services.
    }
}
