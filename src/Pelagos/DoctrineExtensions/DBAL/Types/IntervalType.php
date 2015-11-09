<?php

namespace Pelagos\DoctrineExtensions\DBAL\Types;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\DBALException;

/**
 * Stores and retrieves DateInterval instances as PostgreSQL interval type.
 */
class IntervalType extends Type
{
    /**
     * The Interval type name.
     *
     * @var string
     */
    const INTERVAL = 'interval';

    /**
     * Converts a value from its PHP representation to its database representation of this type.
     *
     * @param \DateInterval    $value    The value to convert.
     * @param AbstractPlatform $platform The currently used database platform.
     *
     * @throws DBALException::notSupported When attempting to use this type for
     *                                     any platform other than PostgreSql.
     *
     * @return string The database representation of the value.
     */
    public function convertToDatabaseValue(\DateInterval $value, AbstractPlatform $platform)
    {
        if (get_class($platform) !== 'Doctrine\DBAL\Platforms\PostgreSqlPlatform') {
            throw DBALException::notSupported(self::INTERVAL . ' type');
        }
        return (null === $value) ? null : $value->format('%s seconds');
    }

    /**
     * Converts a value from its database representation to its PHP representation of this type.
     *
     * @param string           $value    The value to convert.
     * @param AbstractPlatform $platform The currently used database platform.
     *
     * @throws DBALException::notSupported                 When attempting to use this type for
     *                                                     any platform other than PostgreSql.
     * @throws ConversionException::conversionFailedFormat When the value from the database
     *                                                     does not look like an ISO 8601 interval.
     * @throws ConversionException::conversionFailed       When the value from the database
     *                                                     cannot be used to instantiate a \DateInterval.
     *
     * @return \DateInterval The PHP representation of the value.
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (get_class($platform) !== 'Doctrine\DBAL\Platforms\PostgreSqlPlatform') {
            throw DBALException::notSupported(self::INTERVAL . ' type');
        }
        if (null !== $value) {
            $iso8601IntervalRegEx
                = '/^P(?=.)' .
                '(?:\d+Y|Y)?' .
                '(?:\d+M|M)?' .
                '(?:\d+D|D)?' .
                '(?:T(?=.)' .
                    '(?:\d+H|H)?' .
                    '(?:\d+M|M)?' .
                    '(?:\d+' .
                        '(?:\­.\d{1,2})?S|S' .
                    ')?' .
                ')?' .
                '$/';
            if (false === preg_match($iso8601IntervalRegEx, $value)) {
                throw ConversionException::conversionFailedFormat(
                    $value,
                    $this->getName(),
                    $iso8601IntervalRegEx
                );
            }
            try {
                $value = new \DateInterval($value);
            } catch (\Exception $e) {
                throw ConversionException::conversionFailed(
                    $value,
                    $this->getName()
                );
            }
        }
        return $value;
    }

    /**
     * Gets the name of this type.
     *
     * @return string The name of this type.
     */
    public function getName()
    {
        return self::INTERVAL;
    }
}
