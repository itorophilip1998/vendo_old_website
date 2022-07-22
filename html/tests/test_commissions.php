<?php

require_once(__DIR__."/../lib/commissions.php");

use PHPUnit\Framework\TestCase;

final class test_commissions extends TestCase
{
    public function testCalculateRankNone(): void
    {
        $stub = $this->getMockBuilder(Commissions::class)
                    ->onlyMethods(['getDirectActiveDownlineMembers','getTotalActiveDownlineMembers'])
                    ->getMock();

        $stub->method('getDirectActiveDownlineMembers')
            ->will($this->returnValue(array(array('id' => 1),array('id' => 2),array('id' => 3),array('id' => 4),array('id' => 5))));

        $map = [
            [1, 0],
            [2, 0],
            [3, 0],
            [4, 0],
            [5, 0]
        ];

        $stub->method('getTotalActiveDownlineMembers')
            ->will($this->returnValueMap($map));
            
        $level = $stub->calculateCareerlevel(555,0);

        $this->assertEquals(
            Commissions::CAREER_LEVEL_NONE,
            $level
        );
    }

    public function testCalculateRankBlueSapphire(): void
    {
        $stub = $this->getMockBuilder(Commissions::class)
                    ->onlyMethods(['getDirectActiveDownlineMembers','getTotalActiveDownlineMembers'])
                    ->getMock();

        $stub->method('getDirectActiveDownlineMembers')
            ->will($this->returnValue(array(array('id' => 1),array('id' => 2),array('id' => 3),array('id' => 4),array('id' => 5))));

        $map = [
            [1, 1000000], //3
            [2, 2], //2
            [3, 1], //1
            [4, 4], //3
            [5, 0]  //0
        ];

        $stub->method('getTotalActiveDownlineMembers')
            ->will($this->returnValueMap($map));
            
        $level = $stub->calculateCareerlevel(555,0);

        $this->assertEquals(
            Commissions::CAREER_LEVEL_BLUESAPPHIRE,
            $level
        );
    }

    public function testCalculateRankRedRuby(): void
    {
        $stub = $this->getMockBuilder(Commissions::class)
                    ->onlyMethods(['getDirectActiveDownlineMembers','getTotalActiveDownlineMembers'])
                    ->getMock();

        $stub->method('getDirectActiveDownlineMembers')
            ->will($this->returnValue(array(array('id' => 1),array('id' => 2),array('id' => 3),array('id' => 4),array('id' => 5))));

        $map = [// 5 direct + 25% from each
            [1, 1000000], //8
            [2, 4], //5
            [3, 8], //8
            [4, 2], //3
            [5, 80] //8
        ]; 

        $stub->method('getTotalActiveDownlineMembers')
            ->will($this->returnValueMap($map));
            
        $level = $stub->calculateCareerlevel(555,0);

        $this->assertEquals(
            Commissions::CAREER_LEVEL_REDRUBY,
            $level
        );
    }
    
    public function testCalculateGreenEmerald(): void
    {
        $stub = $this->getMockBuilder(Commissions::class)
                    ->onlyMethods(['getDirectActiveDownlineMembers','getTotalActiveDownlineMembers'])
                    ->getMock();

        $stub->method('getDirectActiveDownlineMembers')
            ->will($this->returnValue(array(array('id' => 1),array('id' => 2),array('id' => 3),array('id' => 4),array('id' => 5),array('id' => 6))));

            //99 = one less than 100 for Diamond
            $map = [// 6 direct + 25% from each
                [1, 1000000], //20 / 15
                [2, 55], //20 / 15
                [3, 61], //20 / 15
                [4, 25], //20 / 15
                [5, 10], //10 / 10
                [6, 3]  //3 / 3
            ]; // 99 / 79  => Green Emerald

        $stub->method('getTotalActiveDownlineMembers')
            ->will($this->returnValueMap($map));
            
        $level = $stub->calculateCareerlevel(555,0);

        $this->assertEquals(
            Commissions::CAREER_LEVEL_GREENEMERALD,
            $level
        );
    }
    
    public function testCalculateGreenDiamond(): void
    {
        $stub = $this->getMockBuilder(Commissions::class)
                    ->onlyMethods(['getDirectActiveDownlineMembers','getTotalActiveDownlineMembers'])
                    ->getMock();

        $stub->method('getDirectActiveDownlineMembers')
            ->will($this->returnValue(array(array('id' => 1),array('id' => 2),array('id' => 3),array('id' => 4),array('id' => 5),array('id' => 6))));

        $map = [// 6 direct + 20% from each
            [1, 1000000], //60
            [2, 57], //58
            [3, 61], //60
            [4, 25], //26
            [5, 35], //36
            [6, 88]  //60
        ]; 

        $stub->method('getTotalActiveDownlineMembers')
            ->will($this->returnValueMap($map));
            
        $level = $stub->calculateCareerlevel(555,0);

        $this->assertEquals(
            Commissions::CAREER_LEVEL_GREENDIAMOND,
            $level
        );
    }    

    public function testCalculateCrownPresident(): void
    {
        $stub = $this->getMockBuilder(Commissions::class)
                    ->onlyMethods(['getDirectActiveDownlineMembers','getTotalActiveDownlineMembers'])
                    ->getMock();

        $stub->method('getDirectActiveDownlineMembers')
            ->will($this->returnValue(array(array('id' => 1),array('id' => 2),array('id' => 3),array('id' => 4),array('id' => 5),array('id' => 6),array('id' => 7))));

        $map = [// 7 direct + 15% from each
            [1, 1000000], //10500
            [2, 10000], //10000
            [3, 200000], //10500
            [4, 9000], //9000
            [5, 10000], //10000
            [6, 10000],  //10000
            [7, 9999],  //9999
        ]; 

        $stub->method('getTotalActiveDownlineMembers')
            ->will($this->returnValueMap($map));
            
        $level = $stub->calculateCareerlevel(555,0);

        $this->assertEquals(
            Commissions::CAREER_LEVEL_CROWNPRESIDENT,
            $level
        );
    }  

    public function testgetUsersInPool(): void
    {
        $fakePDO = $this->getMockBuilder(stdClass::class)
                ->addMethods(['prepare'])
                ->getMock();

        $sthStub = $this->getMockBuilder(stdClass::class)
                    ->addMethods(['bindParam','execute','fetchAll'])
                    ->getMock();     
                    
        $fakePDO->method('prepare')
                ->willReturn($sthStub);

        $sthStub->method('execute')
                ->willReturn(true);                

        Commissions::setDatabase($fakePDO);

        $commissions = new Commissions();

        $sthStub->method('fetchAll')
            ->will($this->returnValue(array(
                                            array('id' => 1, 'active_direct_count' => 1, 'month_ago' => 1),
                                            array('id' => 1, 'active_direct_count' => 3, 'month_ago' => 2),
                                            array('id' => 2, 'active_direct_count' => 1, 'month_ago' => 1),
                                            array('id' => 2, 'active_direct_count' => 1, 'month_ago' => 2),
                                            array('id' => 3, 'active_direct_count' => 2, 'month_ago' => 1),
                                            array('id' => 4, 'active_direct_count' => 1, 'month_ago' => 1),
                                            array('id' => 4, 'active_direct_count' => 8, 'month_ago' => 5),
                                            array('id' => 5, 'active_direct_count' => 9, 'month_ago' => 5),      
                                            array('id' => 6, 'active_direct_count' => 2, 'month_ago' => 1),
                                            array('id' => 6, 'active_direct_count' => 2, 'month_ago' => 5),   
                                            array('id' => 7, 'active_direct_count' => 4, 'month_ago' => 2),                                            
                                            array('id' => 7, 'active_direct_count' => 2, 'month_ago' => 5)                                                                                                                          
                                        )));

        $fakePDO->expects($this->exactly(2))
                ->method('prepare')
                ->withConsecutive(
                    [$this->anything(), $this->anything()],
                    [$this->stringContains('IN (1,3,6,7)'), $this->anything()]
                );

        $commissions->getUsersInPool(1);

    }

}
