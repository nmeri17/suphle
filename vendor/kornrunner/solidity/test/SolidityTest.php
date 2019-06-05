<?php

namespace kornrunner;

use BN\BN;
use PHPUnit\Framework\TestCase;

class SolidityTest extends TestCase
{
    private const contractAddress = '0x2a0c0dbecc7e4d658f48e01e3fa353f44050c208';
    private const tokenBuy = '0x0000000000000000000000000000000000000000';
    private const amountBuy = '100000000';
    private const tokenSell = '0x1111111111111111111111111111111111111111';
    private const amountSell = '10000';
    private const address = '0x034767f3c519f361c5ecf46ebfc08981c629d381';
    private const nonce = 5;
    private const expires = '10000';

    /**
     * @dataProvider sha3
     */
    public function testSha3($data, $expect) {
        $this->assertEquals(Solidity::sha3($data), $expect);
    }

    public static function sha3 (): array {
        return [
            [self::contractAddress, '0x9f13f88230a70de90ed5fa41ba35a5fb78bc55d11cc9406f17d314fb67047ac7'],
            [self::tokenBuy, '0x5380c7b7ae81a58eb98d9c78de4a1fd7fd9535fc953ed2be602daaa41767312a'],
            [self::amountBuy, '0xfb05e4134e5b30db022b94b822e7d19b1e5cd1c244468eada63789fd3514454a'],
            [self::tokenSell, '0xe2c07404b8c1df4c46226425cac68c28d27a766bbddce62309f36724839b22c0'],
            [self::amountSell, '0x1d460b64f7b8ba0be629afe9b4ae65333b379985d7ea823ff4c0b8c3b5102153'],
            [self::expires, '0x1d460b64f7b8ba0be629afe9b4ae65333b379985d7ea823ff4c0b8c3b5102153'],
            [self::nonce, '0x036b6384b5eca791c62761152d0c79bb0604c104a5fb6f4eb0703f3154bb3db0'],
            [self::address, '0x5c72003ad77a34d6c7061c57eb81dd46bc248e43cfd5bd64fb43f10c2edb805b'],
            ['0x0a', '0x0ef9d8f8804d174666011a394cab7901679a8944d24249fd148a6a36071151f8'],
            [1, '0xb10e2d527612073b26eecdfd717e6a320cf44b4afac2b0732d9fcbe2b7fa0cf6'],
            [-1, '0xa9c584056064687e149968cbab758a3376d22aedc6a55823d1b3ecbee81b8fb9'],
            ['a', '0x3ac225168df54212a25c1c01fd35bebfea408fdac2e31ddd6f80a4bbf9a5f1cb'],
            [new BN('100'), '0x26700e13983fefbd9cf16da2ed70fa5c6798ac55062a4803121a869731e308d2'],
            ['Hello!%', '0x661136a4267dba9ccdf6bfddb7c00e714de936674c4bdb065a531cf1cb15c7fc'],
            ["Hello!%\u{0000}Terminated", '0x661136a4267dba9ccdf6bfddb7c00e714de936674c4bdb065a531cf1cb15c7fc'],
            ['234', '0x61c831beab28d67d1bb40b5ae1a11e2757fa842f031a2d0bc94a7867bc5d26c2'],
            [0xea, '0x61c831beab28d67d1bb40b5ae1a11e2757fa842f031a2d0bc94a7867bc5d26c2'],
            [new BN('234'), '0x61c831beab28d67d1bb40b5ae1a11e2757fa842f031a2d0bc94a7867bc5d26c2'],
            [-23, '0x2c4c6f97bb4e0ddf0268bb2e6bd2ae2d2db3311c05e6819bc6bcef2df485b4b1'],
            ['0x407D73d8a49eeb85D32Cf465507dd71d507100c1', '0x4e8ebbefa452077428f93c9520d3edd60594ff452a29ac7d2ccc11d47f3ab95b'],
            ['0x85F43D8a49eeB85d32Cf465507DD71d507100C1d', '0xe88edd4848fdce08c45ecfafd2fbfdefc020a7eafb8178e94c5feaeec7ac0bb4'],
            ['234564535', '0xb2daf574dc6ceac97e984c8a3ffce3c1ec19e81cc6b18aeea67b3ac2666f4e97'],
            ['0xfff23243', '0x0ee4597224d3499c72aa0c309b0d0cb80ff3c2439a548c53edb479abfd6927ba'],
            [true, '0x5fe7f977e71dba2ea1a68e21057beebb9be2ac30c6410aa38d4f3fbe41dcffd2'],
            [false, '0xbc36789e7a1e281436464229828f817d6612f7b477d66591ff96a9e064bcc98a'],
            ['0x07fbaab41a7ab8bc6b1b40d74b8e2f69291457a69064e6566000000000000000', '0x043e025381f3f0308037358aa0648f23a2c6fc033c167a88c7079b4dae6bc319'],
            ['0x07fbaab41a7ab8b518a8717694995ae8b9d8a005490235c7a1ea76733c58712b', '0x4c5567b9b77f2bfbbc75c3ff63ddbc2410293e8f711d9f7b0878c4531aaef718'],
        ];
    }

    /**
     * @dataProvider sha3Variadic
     */
    public function testSha3Variadic($args, $expect) {
        $this->assertEquals(call_user_func_array('\kornrunner\Solidity::sha3', $args), $expect);
    }

    public static function sha3Variadic (): array {
        return [
            [[self::contractAddress, self::tokenBuy, self::amountBuy, self::tokenSell, self::amountSell, self::expires, self::nonce, self::address], '0xf20f20d357419f696f69e6ff05bc6566b1e6d38814ce4f489d35711e2fd2c481'],
            [['0x0a', 1], '0xf88b7969914a53d588c819dfc61967e9f4955a6acc93ab0e225ee6d463a592cf'],
            [['a', 1], '0xb5cafab5b83d18303877bb912b2d66ca18ab7390cfd9be8a2e66cc5096e0ea02'],
            [['Hello!%', -23, '0x85F43D8a49eeB85d32Cf465507DD71d507100C1d'], '0xd8de0e57dc8dbe41e10a10f247f16202be05f03bfaff337dc9358c517a172e74'],
            [['234564535', '0xfff23243', true, -10], '0x3e27a893dc40ef8a7f0841d96639de2f58a132be5ae466d40087a2cfa83b7179'],
        ];
    }
}
