<?php

/*
|--------------------------------------------------------------------------
|  Slackhook Message Package
|--------------------------------------------------------------------------
|
|  Copyright: 19 Peaches, LLC.
|  Author:    Vince Kronlein <vince@19peaches.com>
|
|  For the full copyright and license information, please view the LICENSE
|  file that was distributed with this source code.
|
*/

namespace Slackhook\Contracts;

use Slackhook\Slack;

interface TemplateProvider {

    public function __construct(Slack $config);

    public function make(array $data);

}
