<?php

namespace Phormium\Tests;

use \Phormium\DB;
use \Phormium\Tests\Models\Person;

class TransactionTest extends \PHPUnit_Framework_TestCase
{
    public function testManualBeginCommit()
    {
        $person = new Person();
        $person->name = 'Bruce Dickinson';
        $person->income = 12345;
        $person->save();
        
        $id = $person->id;
        
        DB::begin();
        
        $p = Person::get($id);
        $p->income = 54321;
        $p->save();
        
        DB::commit();
        
        self::assertEquals(54321, Person::get($id)->income);
    }
    
    public function testManualBeginRollback()
    {
        $person = new Person();
        $person->name = 'Bruce Dickinson';
        $person->income = 12345;
        $person->save();
        
        $id = $person->id;
        
        DB::begin();
        
        $p = Person::get($id);
        $p->income = 54321;
        $p->save();
        
        DB::rollback();
        
        self::assertEquals(12345, Person::get($id)->income);
    }
    
    public function testCallbackTransactionCommit()
    {
        $person = new Person();
        $person->name = 'Bruce Dickinson';
        $person->income = 12345;
        $person->save();
        
        $id = $person->id;
        
        DB::transaction(function() use ($id) {
            $p = Person::get($id);
            $p->income = 54321;
            $p->save();
        });
        
        self::assertEquals(54321, Person::get($id)->income);
    }
    
    public function testCallbackTransactionRollback()
    {
        $person = new Person();
        $person->name = 'Bruce Dickinson';
        $person->income = 12345;
        $person->save();
        
        $id = $person->id;
        
        try {
            DB::transaction(function() use ($id) {
                $p = Person::get($id);
                $p->income = 54321;
                $p->save();
                
                throw new \Exception("Aborting");
            });
            
            self::fail("This code should not be reachable.");
            
        } catch (\Exception $ex) {
            // Expected. Do nothing.
        }
        
        // Check changes have been rolled back
        self::assertEquals(12345, Person::get($id)->income);
    }
}
