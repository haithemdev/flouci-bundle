<?php

namespace Flouci\SymfonyBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class FlouciSymfonyBundle extends Bundle
{
    public function getPath(): string
    {
        return __DIR__;
    }
}
