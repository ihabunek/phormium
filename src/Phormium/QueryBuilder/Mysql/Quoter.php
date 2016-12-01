<?php

namespace Phormium\QueryBuilder\Mysql;

use Phormium\QueryBuilder\Common\Quoter as CommonQuoter;

class Quoter extends CommonQuoter
{
    protected $left = "`";
    protected $right = "`";
}
