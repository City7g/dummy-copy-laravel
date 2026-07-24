<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

#[Signature("benchmark-redis")]
#[Description("Command description")]
class BenchmarkRedis extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        Cache::forget("users:all");

        // DB напрямую
        $start = microtime(true);
        User::all();
        $dbTime = (microtime(true) - $start) * 1000;

        // Redis — промах кеша (первый запрос)
        $start = microtime(true);
        Cache::remember("users:all", 300, fn() => User::all());
        $cacheMissTime = (microtime(true) - $start) * 1000;

        // Redis — попадание в кеш (уже есть данные)
        $start = microtime(true);
        Cache::remember("users:all", 300, fn() => User::all());
        $cacheHitTime = (microtime(true) - $start) * 1000;

        $this->table(
            ["Источник", "Время (ms)"],
            [
                ["DB напрямую", round($dbTime, 3)],
                ["Redis (miss)", round($cacheMissTime, 3)],
                ["Redis (hit)", round($cacheHitTime, 3)],
            ],
        );
    }
}
