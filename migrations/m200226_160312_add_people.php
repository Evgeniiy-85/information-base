<?php

use yii\db\Migration;

/**
 * Class m200226_160312_add_people
 */
class m200226_160312_add_people extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('people', [
            'ppl_id' => $this->primaryKey(),
            'ppl_itn' => $this->string(16)->notNull()->unique(),
            'ppl_name' => $this->string(32)->notNull(),
            'ppl_surname' => $this->string(32)->notNull(),
            'ppl_middle_name' => $this->string(32)->notNull(),
            'ppl_birth_date' => $this->integer(10)->notNull(),
            'ppl_nationality' => $this->string(32)->notNull(),
            'ppl_place_address' => $this->string()->notNull(),
            'ppl_residence_address' => $this->string()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('people');
    }
}
