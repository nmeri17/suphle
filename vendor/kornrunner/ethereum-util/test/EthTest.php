<?php

use kornrunner\Eth;

class EthTest extends PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider hashPersonalMessage
     */
    public function testHashPersonalMessage($message, $expect)
    {
        $this->assertSame(Eth::hashPersonalMessage($message), $expect);
    }

    public static function hashPersonalMessage(): array {
        return [
            ['f20f20d357419f696f69e6ff05bc6566b1e6d38814ce4f489d35711e2fd2c481', '58a2db04c169254495a55b6dd5609a4902678ec29eac46df1e95994cdbeaebbb'],
            ['0xf20f20d357419f696f69e6ff05bc6566b1e6d38814ce4f489d35711e2fd2c481', '58a2db04c169254495a55b6dd5609a4902678ec29eac46df1e95994cdbeaebbb'],
            ['0xd8de0e57dc8dbe41e10a10f247f16202be05f03bfaff337dc9358c517a172e74', '988d79c9ea9404ed9ae60d3fea39c6df6c878cec5a01e05bacdd5b443e086126'],
            ['0x3e27a893dc40ef8a7f0841d96639de2f58a132be5ae466d40087a2cfa83b7179', '429217acd377a3a2c57dc2d5d12f578c5d11047b6d23f1827d6d3110b95952af'],
        ];
    }

    public function testNonHexadecimal()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Message should be a hexadecimal');
        Eth::hashPersonalMessage(implode(range('a', 'z')));
    }

    public function testOddHex()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Message size cannot be odd');
        Eth::hashPersonalMessage('0xabc');
    }

}
