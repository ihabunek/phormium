<?php

namespace Phormium\QueryBuilder\Common;

class Quoter
{
    protected $left = '"';
    protected $right = '"';

    public function quote($name)
    {
        return $this->left . trim($name) . $this->right;
    }
}
