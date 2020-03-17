<?php
namespace MohdNazrul\CTOSLaravelTest;
use PHPUnit\Framework\TestCase;

class BatchTest extends TestCase{

     public function testEmpty(){
         $records = [];
         $this->assertEmpty($records);

         return $records;
     }

}


