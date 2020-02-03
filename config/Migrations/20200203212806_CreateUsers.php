<?php

declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateUsers extends AbstractMigration
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
        $table = $this->table('users');

        $table->addColumn('username', 'string', [
            'limit' => 45,
            'null' => false,
        ]);
        $table->addColumn('email', 'string', [
            'limit' => 255,
            'null' => false
        ]);
        $table->addColumn('first_name', 'string', [
            'limit' => 255,
            'null' => true,
        ]);
        $table->addColumn('last_name', 'string', [
            'limit' => 255,
            'null' => true,
        ]);

        $table->addColumn('staged', 'boolean', [
            'default' => false,
        ]);
        $table->addColumn('created', 'datetime');
        $table->addColumn('modified', 'datetime');


        $table->create();
    }

    public function down()
    {
        $table = $this->table('users');
        $table->drop();
    }
}
