<?php

namespace App\Support;

use Aws\CommandInterface;

class StripS3ObjectAcl
{
    public static function beforeUpload(CommandInterface $command): void
    {
        unset($command['ACL']);
    }
}
