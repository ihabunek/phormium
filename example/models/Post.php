<?php

/**
 * A blog post.
 *
 * Demonstrates usage of composite primary key.
 */
class Post extends \Phormium\Model
{
    protected static $_meta = array(
        'database' => 'exampledb',
        'table' => 'post',
        'pk' => ['date', 'no']
    );

    public $date;

    public $no;

    public $title;

    public $contents;

    public function tags()
    {
        return $this->hasChildren('Tag');
    }
}
