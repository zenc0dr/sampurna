<?php

namespace Zenc0dr\Sampurna\Classes;

class SampurnaDaemon
{
    public static function run()
    {
        self::repeatProtection();
        $i = 0;
        while (true) {
            self::checkState();
            self::savePid();
            $i++;
            sampurna()->dispatcher()->run();
            sleep(2);
        }
    }

    public static function pidPath(): string
    {
        return config('sampurna.sampurna_vault') . '/pid';
    }

    public static function statePath(): string
    {
        return config('sampurna.sampurna_vault') . '/daemon_enabled';
    }

    public static function savePid(): void
    {
        file_put_contents(
            sampurna()->helpers()->checkDir(
                self::pidPath()
            ),
            getmypid()
        );
    }

    public static function enableDaemon(): void
    {
        file_put_contents(
            sampurna()->helpers()->checkDir(
                self::statePath()
            ),
            now()->format('Y-m-d H:i:s')
        );
        sampurna()
            ->services()
            ->log("Демон активирован " . now()->format('Y-m-d H:i:s'));
    }

    public static function disableDaemon(): void
    {
        if (file_exists(self::statePath())) {
            unlink(self::statePath());
            sampurna()
                ->services()
                ->log("Демон деактивирован " . now()->format('Y-m-d H:i:s'));
        }
    }

    public static function repeatProtection(): void
    {
        if (file_exists(self::pidPath())) {
            $saved_pid = intval(file_get_contents(self::pidPath()));
            if (sampurna()->services()->pidIsActive($saved_pid)) {
                exit(0);
            }
        }
    }

    private static function checkState(): void
    {
        if (!file_exists(self::statePath())) {
            sampurna()
                ->services()
                ->log("Демон остановился " . now()->format('Y-m-d H:i:s'));
            exit(0);
        }
    }
}