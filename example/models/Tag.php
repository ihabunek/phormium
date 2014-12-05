<?php

class Tag extends \Phormium\Model
{
    protected static $_meta = array(
        'database' => 'exampledb',
        'table' => 'tag',
        'pk' => 'id'
    );

    public $id;

    public $post_date;

    public $post_no;

    public $value;

    public function post()
    {
        return $this->hasParent("Post");
    }
}
