<?php

namespace Abbadon1334\Token;


use Atk4\Data\Exception;
use Atk4\Data\Persistence;
use Atk4\Data\Schema\Migrator;

class TokenTest extends \Codeception\Test\Unit
{
    private Token $model;
    private Persistence $persistence;

    protected function _before()
    {
        parent::_before();

        $this->persistence = new \Atk4\Data\Persistence\Sql('sqlite:tests/_data/sqlite.db');

        $migrator = new Migrator($this->getModel());
        $migrator->dropIfExists()->create();
    }

    private function getModel() : Token {
        return new Token($this->persistence);
    }

    public function testGetNewToken()
    {
        $model = $this->getModel();

        $token1 = $model->getNewToken('test', null, 10);
        $token2 = $model->getNewToken('test2', null, 10);

        $this->assertNotEquals($token1->get('code'), $token2->get('code'));
    }

    public function testLoadByTypeAndCode()
    {
        $this->getModel()->getNewToken('test', 'load', 0);

        $model = $this->getModel();
        $entity = $model->loadByTypeAndCode('test', 'load');

        $this->assertTrue($entity->isLoaded());
    }

    public function testDeleteByTypeAndCode()
    {
        $this->getModel()->getNewToken('test', 'delete', 0);

        $model = $this->getModel();
        $model->deleteByTypeAndCode('test', 'delete');

        $model = $this->getModel();
        $entity = $model->loadByTypeAndCode('test', 'delete');

        $this->assertFalse($entity->isLoaded());
    }

    public function testIsExpired()
    {
        $model = $this->getModel();
        $token = $model->getNewToken('test', null, 0);
        $this->assertTrue($token->isExpired());
    }


    public function testGetValue()
    {
        $model = $this->getModel();
        $token = $model->getNewToken('test', 'test-value');

        $token->set('value', [
            'key' => 'value'
        ]);

        $token->save();

        $model = $this->getModel();
        $entity = $model->loadByTypeAndCode('test', 'test-value');

        $this->assertEquals('value', $entity->get('value')['key']);
    }
}
