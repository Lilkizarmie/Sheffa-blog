<?php

namespace Database\Seeders;

use Botble\Setting\Models\Setting as SettingModel;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SettingModel::whereIn('key', ['media_random_hash'])->delete();
        SettingModel::insertOrIgnore([
            [
                'key'   => 'media_random_hash',
                'value' => md5(time()),
            ],
            [
                'key'   => 'comment_enable',
                'value' => 1,
            ],
            [
                'key' => 'comment_menu_enable',
                'value' => json_encode(["Botble\Blog\Models\Post"]),
            ]
        ]);
    }
}
