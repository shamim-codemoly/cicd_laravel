<?php

namespace App\Console\Commands;

use App\Enums\SettingType;
use App\Models\Setting;
use App\Models\Shop;
use Illuminate\Console\Command;

class SyncSettingCommand extends Command
{
    protected $signature = 'setting:sync {key}';
    protected $description = 'Sync a specific setting key for all shops if missing';

    public function handle()
    {
        $key = $this->argument('key');

        // Get all shops
        $shops = Shop::all();

        foreach ($shops as $shop) {
            $exists = Setting::where('shop_id', $shop->id)
                ->where('key', $key)
                ->exists();

            if (! $exists) {
                Setting::create([
                    'shop_id' => $shop->id,
                    'key'     => $key,
                    'value' => null,
                    'type' => SettingType::VENDOR->value,
                    'is_active' => true,
                ]);

                $this->info("Added missing setting '{$key}' for Shop ID: {$shop->id}");
            } else {
                $this->line("Setting '{$key}' already exists for Shop ID: {$shop->id}");
            }
        }

        $this->info('Sync complete.');
    }
}
