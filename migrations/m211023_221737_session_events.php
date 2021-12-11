<?php

use yii\db\Migration;

/**
 * Class m211023_221737_session_events
 */
class m211023_221737_session_events extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        // `Person` table creation
        $this->createTable('{{%Person}}', [
            'id' => $this->primaryKey()->unsigned(),
            'createDate' => $this->dateTime()->null(),
            'cookieStringId' => $this->string(32)->unique()->notNull(),
        ]);
        
        //create `Session` table
        $this->createTable('{{%Session}}', [
            'id' => $this->primaryKey()->unsigned(),
            'createDate' => $this->dateTime()->null(),
            'updateDate' => $this->dateTime()->null(),
            'personId' => $this->integer()->unsigned()->notNull(),
            'sessionStringId' => $this->string(32)->null(),
            'cookieParams' => $this->text()->null(),
            'serverParams' => $this->text()->null(),
            'type' => $this->string(16)->null(),
            'model' => $this->string(64)->null(),
            'platform' => $this->string(64)->null(),
            'platformVersion' => $this->string(16)->null(),
            'browser' => $this->string(128)->null(),
            'isBot' => $this->tinyInteger(1)->notNull()->defaultValue(0),
            'cost' => $this->decimal(12)->notNull()->defaultValue(0),
            'income' => $this->decimal(12)->notNull()->defaultValue(0)
        ]);
        
        // add foreign key for column `statusId` linked with table `PersonStatus`
        $this->addForeignKey(
            'fk-Session-personId',
            '{{%Session}}',
            'personId',
            '{{%Person}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        
        // create index for column `sessionId`
        $this->createIndex(
            'idx-Session-sessionStringId',
            '{{%Session}}',
            'sessionStringId'
        );
        
        $this->createTable('{{%SessionUrl}}', [
            'id' => $this->primaryKey()->unsigned(),
            'createDate' => $this->dateTime()->null(),
            'sessionId' => $this->integer()->unsigned()->notNull(),
            'visitedUrl' => $this->string(500)->null(),
            'duration' => $this->smallInteger()->notNull()->unsigned()->defaultValue(0),
            'scrollsDown' => $this->smallInteger()->notNull()->unsigned()->defaultValue(0),
            'scrollsUp' => $this->smallInteger()->notNull()->unsigned()->defaultValue(0),
        ]);
        
        $this->addForeignKey(
            'fk-SessionUrl-sessionId',
            '{{%SessionUrl}}',
            'sessionId',
            '{{%Session}}',
            'id',
            'CASCADE'
        );
        
        $this->createIndex('idx_SessionUrl_createDate_visitedUrl', '{{%SessionUrl%}}', ['createDate', 'visitedUrl']);
        
        $this->createTable('{{%SessionEvent}}', [
            'id' => $this->primaryKey()->unsigned(),
            'createDate' => $this->dateTime(),
            'sessionUrlId' => $this->integer()->unsigned()->null(),
            'eventName' => $this->string(100)->notNull(),
            'params' => $this->text()->null(),
        ]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function down()
    {
        
        $this->dropForeignKey('fk-SessionUrl-sessionId', '{{%SessionUrl%}}');
        $this->dropIndex('idx_SessionUrl_createDate_visitedUrl', '{{%SessionUrl%}}');
        
        $this->dropTable('{{%SessionUrl}}');
        
        $this->dropTable('{{%SessionEvent}}');
        
        // drops index for column `sessionId`
        $this->dropIndex(
            'idx-Session-sessionId',
            '{{%Session}}'
        );
        
        // drops table `Session`
        $this->dropTable('{{%Session}}');
        
        $this->dropForeignKey('fk-Session-personId', '{{%Session}}');
        
        $this->dropTable('{{%Person}}');
        
        return false;
    }
}
