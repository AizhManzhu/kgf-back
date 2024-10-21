<?php

namespace App\Traits;

trait Helpers
{

    public function camelize(string $input): string
    {
        return lcfirst(str_replace('_', '', ucwords($input, '_')));
    }

}
