<?php


use Kelvinho\Notes\Id\IdGenerator;
use Kelvinho\Notes\Id\IdGeneratorImp;
use PHPUnit\Framework\TestCase;

class IdGeneratorTest extends TestCase {
    /** @var IdGenerator */
    private $idGenerator;

    public function setUp(): void {
        $this->idGenerator = new IdGeneratorImp($this->createMock("\mysqli"));
    }

    public function testNewVirusId() {
        $this->assertEquals(64, strlen($this->idGenerator->newVirusId()));
    }

    public function testNewAttackId() {
        $this->assertEquals(64, strlen($this->idGenerator->newAttackId()));
    }
}