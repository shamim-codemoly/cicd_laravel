<?php

namespace App\Console\Commands;

use App\Enums\SettingType;
use App\Models\Setting;
use App\Models\Shop;
use Illuminate\Console\Command;

class SyncAllSettingsCommand extends Command
{
    protected $signature = 'settings:sync-all';
    protected $description = 'Sync all setting keys for each shop if missing';

    protected $defaultKeys = [
        'site_name',
        'store_domain_name',
        'site_color_logo',
        'site_meta_image',
        'site_bw_logo',
        'site_payment_logo',
        'site_slogan',
        'special_message',
        'site_email',
        'site_phone',
        'site_address',
        'site_facebook',
        'site_twitter',
        'site_instagram',
        'site_youtube',
        'estimated_delivery',
        'site_tiktok',
        'site_whatsapp',
        'is_vat_applicable',
        'pathao_base_url',
        'pathao_store_id',
        'pathao_clinet_id',
        'pathao_secret_id',
        'pathao_username',
        'pathao_password',
        'pathao_grant_type',
        'inside_dhaka_cost',
        'outside_dhaka_cost',
        'shipping_costs',
        'default_account',
        'payment_method',
        'product_card_type',
        'style',
        'facebook_pixel_code',
        'facebook_domain_verification',
        'google_analytics',
        'google_domain_verification',
        'google_body_tag',
        'google_merchant_code',
        'tiktok_pixel_code',
        'pinterest_pixel_code',
        'customer_sms_notification',
        'woocommerce_store_url',
        'woocommerce_consumer_key',
        'woocommerce_secret_key',
        'site_meta_title',
        'site_meta_description',
        'site_meta_keywords',
    ];

    public function handle()
    {
        $shops = Shop::all();
        foreach ($shops as $shop) {
            foreach ($this->defaultKeys as $key) {
                $exists = Setting::where('shop_id', $shop->id)->where('key', $key)->exists();

                if (! $exists) {
                    Setting::create([
                        'shop_id' => $shop->id,
                        'key' => $key,
                        'value' => null,
                        'type' => SettingType::VENDOR->value,
                        'is_active' => true,
                    ]);

                    $this->info("Added missing setting '{$key}' for Shop ID: {$shop->id}");
                } else {
                    $this->line("Setting '{$key}' already exists for Shop ID: {$shop->id}");
                }
            }
        }

        $this->info('All settings sync complete.');
    }
}
