<?php

class m161010_050925_user_tbl extends CDbMigration
{
	public function up()
	{
	    $this->createTable('user', [
	        'id' => 'pk',
            'username' => 'varchar(255) NOT NULL',
            'password' => 'varchar(255) NOT NULL',
            'email' => 'varchar(255) NOT NULL',
            'active' => 'tinyint(1) DEFAULT 1 NOT NULL',
        ]);
	}

	public function down()
	{
		$this->dropTable('user');
        return true;
	}

}