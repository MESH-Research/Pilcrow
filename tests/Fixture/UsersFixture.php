<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * UsersFixture
 */
class UsersFixture extends TestFixture
{
    public $import = ['table' => 'users'];

    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'username' => 'test',
                'email' => 'test@example.com',
                'first_name' => 'Joe',
                'last_name' => 'Smith',
                'staged' => 0,
                'created' => '2020-02-03 21:58:11',
                'modified' => '2020-02-03 21:58:11',
                'password' => '',
            ],
        ];
        parent::init();
    }
}
