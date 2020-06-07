<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * NotificationsFixture
 */
class NotificationsFixture extends TestFixture
{
    public $import = ['table' => 'notifications'];
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
                'from_user' => 1,
                'to_user' => 1,
                'content' => 'Test Notification',
                'created' => '2020-02-25 18:15:21',
                'modified' => '2020-02-25 18:15:21',
                'is_read' => 0,
                'is_archived' => 0,
            ],
        ];
        parent::init();
    }
}
