<?php
/**
 * @author Aaron Francis <aarondfrancis@gmail.com|https://twitter.com/aarondfrancis>
 */

namespace App\Sidecar;

use Hammerstone\Sidecar\LambdaFunction;
use Hammerstone\Sidecar\Package;
use Hammerstone\Sidecar\Runtime;

class BasicFunction extends LambdaFunction
{

    public function handler()
    {
        return 'sidecar/basic@handler';
    }

    public function runtime()
    {
        return Runtime::NODEJS_14;
    }

    public function package()
    {
        return [
            'sidecar/basic.js'
        ];
    }
}
