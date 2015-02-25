<?php

class City extends Phormium\Model
{
    protected static $_meta = [
        'database' => 'test',
        'table' => 'city',
        'pk' => 'id'
    ];

    public $id;
    public $name;
    public $countrycode;
    public $district;
    public $population;
}

class Country extends Phormium\Model
{
    protected static $_meta = [
        'database' => 'test',
        'table' => 'country',
        'pk' => 'code'
    ];

    public $code;
    public $name;
    public $continent;
    public $region;
    public $surfacearea;
    public $indepyear;
    public $population;
    public $lifeexpectancy;
    public $gnp;
    public $gnpold;
    public $localname;
    public $governmentform;
    public $headofstate;
    public $capital;
    public $code2;
}