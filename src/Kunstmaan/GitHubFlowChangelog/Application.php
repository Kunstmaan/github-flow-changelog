<?php
namespace Kunstmaan\GitHubFlowChangelog;

use Cilex\Provider\Console\ContainerAwareApplication as BaseApplication;

class Application extends BaseApplication
{
    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        parent::__construct('gfc', 1.0);
    }
}
