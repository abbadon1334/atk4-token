<?php

declare(strict_types=1);

namespace Abbadon1334\TokenTests\Unit;

use Abbadon1334\Token\Token;
use Atk4\Data\Persistence;
use Atk4\Data\Schema\Migrator;

class TokenTest extends \Codeception\Test\Unit
{
    private Token $model;
    private static ?Persistence $persistence = null;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$persistence = new \Atk4\Data\Persistence\Sql('sqlite:tests/_data/sqlite.db');

        $model = new Token(self::$persistence);

        $migrator = new Migrator($model);
        $migrator->dropIfExists()->create();
    }

    private function getModel(): Token
    {
        return new Token(self::$persistence);
    }

    public function testGetNewToken()
    {
        $model = $this->getModel();

        $token1 = $model->getNewToken('test', null, 10);
        $token2 = $model->getNewToken('test2', null, 10);

        $this->assertNotSame($token1->get('code'), $token2->get('code'));
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

        $this->assertNull($entity);
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
            'key' => 'value',
        ]);

        $token->save();

        $model = $this->getModel();
        $entity = $model->loadByTypeAndCode('test', 'test-value');

        $this->assertSame('value', $entity->get('value')['key']);
    }

    public function testPrune()
    {
        $model = $this->getModel();
        $model->addCondition('expire', '<', new \DateTime());

        $this->assertGreaterThan(0, (int) $model->action('count')->getOne());

        $this->getModel()->pruneExpired();

        $model = $this->getModel();
        $model->addCondition('expire', '<', new \DateTime());

        $this->assertSame(0, (int) $model->action('count')->getOne());
    }
}
