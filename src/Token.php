<?php

declare(strict_types=1);

namespace Abbadon1334\Token;

use Atk4\Data\Exception;
use Atk4\Data\Model;
use Datetime;
use Ramsey\Uuid\Uuid;

class Token extends Model
{
    public $table = 'token';

    protected function init(): void
    {
        parent::init();

        $this->addField('type');
        $this->addField('code');
        $this->addField('expire', ['type' => 'datetime']);
        $this->addField('value', ['serialize' => 'serialize']);
    }

    public function loadByTypeAndCode(string $type, string $code): self
    {
        $this->addCondition('type', $type);
        $this->addCondition('code', $code);
        $this->tryLoadAny();

        return $this;
    }

    public function deleteByTypeAndCode(string $type, string $code): void
    {
        $this->loadByTypeAndCode($type, $code)->delete();
    }

    public function isExpired(): bool
    {
        return $this->get('expire') <= (new DateTime());
    }

    public function getNewToken(string $type, string $code = null, int $expireInSeconds = 3600, $value = []): self
    {
        if (empty($type)) {
            throw new Exception('Token $type must be not empty');
        }

        if ($code !== null && empty($code)) {
            throw new Exception('Token $code must be null or not empty');
        }

        if ($expireInSeconds < 0) {
            throw new Exception('Token $expire must be greater than zero');
        }

        $model = new static($this->persistence);
        $model->set('type', $type);
        $model->set('code', $code ?? $this->generateUuid());
        $model->set('expire', (new Datetime())->modify('+' . $expireInSeconds . ' SECONDS')->format('Y-m-d H:i:s'));
        $model->set('value', $value);
        $model->save();

        return $model;
    }

    protected function generateUuid(): string
    {
        return Uuid::uuid4()->toString();
    }
}
