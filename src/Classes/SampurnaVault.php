<?php

namespace Zenc0dr\Sampurna\Classes;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Exception;
use Closure;

class SampurnaVault
{
    private string $vault_name;
    private string $vault_path;
    private static array $instances = [];

    public static function getInstance(string $vault_name): self
    {
        if (isset(self::$instances[$vault_name])) {
            return self::$instances[$vault_name];
        }

        return self::$instances[$vault_name] = new self($vault_name);
    }

    public function __construct(string $vault_name)
    {
        $this->vault_name = $vault_name;
        $this->vault_path = config('sampurna.sampurna_vault') . "/vaults/$vault_name.sqlite";
    }

    public function create(Closure $schema): void
    {
        $this->truncate();
        sampurna()->helpers()->checkDir($this->vault_path);
        touch($this->vault_path);
        config(['database.connections.sqlite_' . $this->vault_name => [
            'driver' => 'sqlite',
            'database' => $this->vault_path,
            'prefix' => '',
        ]]);
        $schema(Schema::connection("sqlite_$this->vault_name"));
    }

    public function update(Closure $schema): void
    {
        if (!file_exists($this->vault_path)) {
            throw new Exception("{$this->vault_path} does not exist");
        }
        config(['database.connections.sqlite_' . $this->vault_name => [
            'driver' => 'sqlite',
            'database' => $this->vault_path,
            'prefix' => '',
        ]]);
        $schema(Schema::connection("sqlite_$this->vault_name"));
    }

    public function query(string $table_name, ?int $id = null, string $id_name = 'id'): ?object
    {
        config(['database.connections.sqlite_' . $this->vault_name => [
            'driver' => 'sqlite',
            'database' => $this->vault_path,
            'prefix' => '',
        ]]);

        $db_connection = DB::connection("sqlite_$this->vault_name");
        $pdo = $db_connection->getPdo();

        # Переопределяем функцию like в sqlite для корректной работы регистронезависимого поиска
        $pdo->sqliteCreateFunction('like', function ($pattern, $value) {
            $pattern = str_replace('%', '.*', preg_quote($pattern, '/'));
            return preg_match('/^' . $pattern . '$/iu', $value) > 0;
        }, 2);

        if ($id) {
            if ($id_name === 'id') {
                return $db_connection->table($table_name)->find($id);
            } else {
                return $db_connection->table($table_name)->where($id_name, $id)->first();
            }
        }

        return $db_connection->table($table_name);
    }

    public function truncate(): void
    {
        if (file_exists($this->vault_path)) {
            unlink($this->vault_path);
        }
    }
}