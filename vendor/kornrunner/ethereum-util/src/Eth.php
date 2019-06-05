<?php

namespace kornrunner;

use InvalidArgumentException;

final class Eth {
    private const HASH_SIZE = 256;

    public static function hashPersonalMessage(string $message): string {
        if (stripos($message, '0x') === 0) {
            $message = substr($message, 2);
        }

        if (!ctype_xdigit($message)) {
            throw new InvalidArgumentException('Message should be a hexadecimal');
        }

        if (strlen($message) % 2) {
            throw new InvalidArgumentException('Message size cannot be odd');
        }

        $buffer = unpack('C*', hex2bin($message));
        $prefix = bin2hex("\u{0019}Ethereum Signed Message:\n" . sizeof($buffer));
        return Keccak::hash(hex2bin($prefix . $message), self::HASH_SIZE);
    }
}
