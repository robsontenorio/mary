<?php

namespace Mary\Traits;

trait WithUuid
{
    public string $uuid;

    public function setUuid(string $uuid)
    {
        $this->uuid = $uuid;
    }
}
