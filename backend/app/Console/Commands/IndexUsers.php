<?php
declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use TeamTNT\TNTSearch\TNTSearch;

class IndexUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'index:users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Index the users table';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $config = [
            'driver'    => env('DB_CONNECTION'),
            'host'      => env('DB_HOST', '127.0.0.1'),
            'database'  => env('DB_NAME'),
            'username'  => env('DB_USERNAME'),
            'password'  => env('DB_PASSWORD'),
            'storage'   => 'backend/storage/app/tntsearch',
            'stemmer'   => \TeamTNT\TNTSearch\Stemmer\NoStemmer::class, // optional
        ];
        $tnt = new TNTSearch();
        $tnt->loadConfig($config);
        $indexer = $tnt->createIndex('users.index');
        $indexer->query('SELECT id, name, email, username FROM users;');
        $indexer->run();
    }
}
