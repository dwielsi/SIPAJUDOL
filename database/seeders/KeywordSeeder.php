<?php

namespace Database\Seeders;

use App\Models\Keyword;
use Illuminate\Database\Seeder;

class KeywordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $keywords = [
            'slot', 'slot gacor', 'casino', 'bet', 'judol', 'pragmatic',
            'mahjong', 'joker', 'scatter', 'maxwin', 'roulette', 'baccarat', 'togel',
        ];

        foreach ($keywords as $keyword) {
            Keyword::firstOrCreate(
                ['keyword' => $keyword],
                ['category' => 'judol', 'active' => true],
            );
        }
    }
}
