<?php
declare(strict_types=1);

namespace App\Traits;

trait WithConnectionName
{
    private $driver;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->connectionName = $this->connection ?? config('database.default');
    }

    /**
     * Check if the current database connection matches the argument
     *
     * @param string $connection
     * @return bool
     */
    public function connectionIs(string $connection): bool
    {
        return $this->connectionName === $connection;
    }
}
