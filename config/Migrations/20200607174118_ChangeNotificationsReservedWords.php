<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class ChangeNotificationsReservedWords extends AbstractMigration
{
    /**
     * Change Method.
     * 
     * Update columns names to not use mysql reserved words. Using them required
     * updating the quoting options in CakePHP which causes a modest performance
     * hit.  Also underscore column names.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     */
    public function change()
    {
        $table = $this->table('notifications');
        $table->renameColumn('read', 'isRead');
        $table->renameColumn('archived', 'isArchived');
        $table->renameColumn('fromuser', 'from_user');
        $table->renameColumn('touser', 'to_user');
        
        $table->update();
    }
}
