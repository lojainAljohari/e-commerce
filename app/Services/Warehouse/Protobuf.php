<?php

namespace App\Services\Warehouse;

use RuntimeException;


final class Protobuf
{
    public static function varint(int $value): string
    {
        $out = '';
        while ($value > 0x7F) {
            $out .= chr(($value & 0x7F) | 0x80);
            $value >>= 7;
        }

        return $out.chr($value);
    }


    public static function intField(int $field, int $value): string
    {
        return $value === 0 ? '' : self::varint(($field << 3) | 0).self::varint($value);
    }


    public static function stringField(int $field, string $value): string
    {
        return $value === '' ? '' : self::varint(($field << 3) | 2).self::varint(strlen($value)).$value;
    }


    public static function messageField(int $field, string $bytes): string
    {
        return self::varint(($field << 3) | 2).self::varint(strlen($bytes)).$bytes;
    }

    /**
     * @return array<int, list<int|string>>
     */
    public static function decode(string $data): array
    {
        $fields = [];
        $i = 0;
        $length = strlen($data);

        while ($i < $length) {
            $tag = self::readVarint($data, $i);
            $field = $tag >> 3;
            $wireType = $tag & 7;

            switch ($wireType) {
                case 0:
                    $value = self::readVarint($data, $i);
                    break;
                case 2:
                    $size = self::readVarint($data, $i);
                    $value = substr($data, $i, $size);
                    $i += $size;
                    break;
                case 1:
                    $value = substr($data, $i, 8);
                    $i += 8;
                    break;
                case 5:
                    $value = substr($data, $i, 4);
                    $i += 4;
                    break;
                default:
                    throw new RuntimeException('Unsupported protobuf wire type '.$wireType);
            }

            $fields[$field][] = $value;
        }

        return $fields;
    }

    private static function readVarint(string $data, int &$i): int
    {
        $result = 0;
        $shift = 0;

        while (true) {
            if (! isset($data[$i])) {
                throw new RuntimeException('Truncated protobuf message');
            }
            $byte = ord($data[$i++]);
            $result |= ($byte & 0x7F) << $shift;
            if (($byte & 0x80) === 0) {
                return $result;
            }
            $shift += 7;
        }
    }
}
