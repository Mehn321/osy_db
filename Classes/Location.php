<?php

/**
 * Official location options for Municipality of Panaon profiling.
 */
class Location
{
    public const DEFAULT_PROVINCE = 'Misamis Occidental';
    public const DEFAULT_MUNICIPALITY = 'Panaon';

    public static function suffixes()
    {
        return ['Jr.', 'Sr.', 'I', 'II', 'III', 'IV', 'V'];
    }

    public static function provinces()
    {
        return [self::DEFAULT_PROVINCE];
    }

    public static function municipalities($province = '')
    {
        if ($province === '' || strcasecmp($province, self::DEFAULT_PROVINCE) === 0) {
            return [self::DEFAULT_MUNICIPALITY];
        }
        return [];
    }

    public static function isValidSuffix($suffix)
    {
        $suffix = trim((string) $suffix);
        return $suffix === '' || in_array($suffix, self::suffixes(), true);
    }

    public static function isValidProvince($province)
    {
        return in_array($province, self::provinces(), true);
    }

    public static function isValidMunicipality($province, $municipality)
    {
        return in_array($municipality, self::municipalities($province), true);
    }
}
