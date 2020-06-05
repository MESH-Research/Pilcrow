<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateNotifications extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     * @return void
     */
    public function up()
    {
        $table = $this->table('notifications');

        $table->addColumn('fromuser', 'integer', [
                'null'=>true
        ]);

        $table->addColumn('touser', 'integer', [
                'null'=>true
        ]);
        
        $table->addColumn('content', 'text',[
                'null'=>true
        ]);        
        
        $table->addColumn('created', 'datetime');

        $table->addColumn('modified', 'datetime');

        $table->addcolumn('read', 'integer', [
		'default'=>0,
		'limit'=>1
        ]);

        $table->addcolumn('archived', 'integer', [
		'default'=>0,
		'limit'=>1
        ]);

        $table->addForeignKey('fromuser', 'users', 'id', [
                'delete'=> 'SET_NULL',
                'update'=> 'NO_ACTION'
        ]);

        $table->addForeignKey('touser', 'users', 'id', [
                'delete'=> 'SET_NULL',
                'update'=> 'NO_ACTION'
	]);

	$table->create();
    }

    public function down()
    {
        $table = $this->table('notifications');
        $table->drop();
    }            
 }
