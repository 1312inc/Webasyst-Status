<?php

/**
 * Class statusStoreColor
 */
final class statusStoreColor
{
    const NONE    = 'none';
    const GRAY1   = 'gray1';
    const GRAY2   = 'gray2';
    const GRAY3   = 'gray3';
    const RED1    = 'red1';
    const RED2    = 'red2';
    const RED3    = 'red3';
    const GREEN1  = 'green1';
    const GREEN2  = 'green2';
    const GREEN3  = 'green3';
    const BLUE1   = 'blue1';
    const BLUE2   = 'blue2';
    const BLUE3   = 'blue3';
    const YELLOW1 = 'yellow1';
    const YELLOW2 = 'yellow2';
    const YELLOW3 = 'yellow3';
    const PURPLE  = 'purple';
    const ORANGE  = 'orange';
    const BROWN   = 'brown';

    /**
     * @return array
     */
    public static function getColors()
    {
        return [
            self::NONE    => _w(self::NONE),
            self::GRAY1  => _w(self::GRAY1),
            self::GRAY2  => _w(self::GRAY2),
            self::GRAY3  => _w(self::GRAY3),
            self::RED1    => _w(self::RED1),
            self::RED2    => _w(self::RED2),
            self::RED3    => _w(self::RED3),
            self::GREEN1  => _w(self::GREEN1),
            self::GREEN2  => _w(self::GREEN2),
            self::GREEN3  => _w(self::GREEN3),
            self::BLUE1   => _w(self::BLUE1),
            self::BLUE2   => _w(self::BLUE2),
            self::BLUE3   => _w(self::BLUE3),
            self::PURPLE  => _w(self::PURPLE),
            self::YELLOW1 => _w(self::YELLOW1),
            self::YELLOW2 => _w(self::YELLOW2),
            self::YELLOW3 => _w(self::YELLOW3),
            self::BROWN   => _w(self::BROWN),
            self::ORANGE  => _w(self::ORANGE),
        ];
    }
}
