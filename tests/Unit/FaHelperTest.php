<?php

namespace Tests\Unit;

use App\Helpers\Fa;
use PHPUnit\Framework\TestCase;

class FaHelperTest extends TestCase
{
    public function test_mobile_is_normalised(): void
    {
        $this->assertSame('09121234567', Fa::mobile('۰۹۱۲۱۲۳۴۵۶۷'));
        $this->assertSame('09121234567', Fa::mobile('+98 912 123 4567'));
        $this->assertSame('09121234567', Fa::mobile('9121234567'));
        $this->assertSame('09121234567', Fa::mobile('0098-912-1234567'));
        $this->assertSame('12345', Fa::mobile('12345'));
    }

    public function test_digits_and_money(): void
    {
        $this->assertSame('123', Fa::digits('۱۲۳'));
        $this->assertSame('۱۲,۰۰۰,۰۰۰', Fa::money(12000000));
    }
}
