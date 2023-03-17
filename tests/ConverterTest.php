<?php

/**
 * VuFindDate Test Class
 *
 * PHP version 7
 *
 * Copyright (C) Villanova University 2011.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category VuFind
 * @package  Tests
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Ere Maijala <ere.maijala@helsinki.fi>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Page
 */

namespace VuFindTest\Date;

use VuFind\Date\Converter;
use VuFind\Date\DateException;

/**
 * VuFindDate Test Class
 *
 * @category VuFind
 * @package  Tests
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Ere Maijala <ere.maijala@helsinki.fi>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Page
 */
class ConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test citation generation
     *
     * @return void
     */
    public function testDates()
    {
        // Get current default time zone
        $real_zone = date_default_timezone_get();

        // Try all the tests in different time zones to ensure consistency:
        foreach (['America/New_York', 'Europe/Helsinki'] as $zone) {
            date_default_timezone_set($zone);
            $this->runTests();
        }

        // Restore original time zone
        date_default_timezone_set($real_zone);
    }

    /**
     * Support method for testDates()
     *
     * @return void
     */
    protected function runTests()
    {
        // Build an object to test with (using empty configuration to ensure default
        // settings):
        $date = new Converter();

        // Try some conversions:
        $this->assertEquals(
            '11-29-1973',
            $date->convertToDisplayDate('U', 123456879)
        );
        $this->assertEquals(
            '11-29-1973',
            $date->convertToDisplayDate('U', 123456879.1234)
        );
        $this->assertEquals(
            '11-29-1973--16:34',
            $date->convertToDisplayDateAndTime('U', 123456879, '--')
        );
        $this->assertEquals(
            '16:34 11-29-1973',
            $date->convertToDisplayTimeAndDate('U', 123456879)
        );
        $this->assertEquals(
            '11-29-1973',
            $date->convertToDisplayDate('m-d-y', '11-29-73')
        );
        $this->assertEquals(
            '11-29-1973',
            $date->convertToDisplayDate('m-d-y', '11-29-1973')
        );
        $this->assertEquals(
            '11-29-1973',
            $date->convertToDisplayDate('m-d-y H:i', '11-29-73 23:01')
        );
        $this->assertEquals(
            '23:01',
            $date->convertToDisplayTime('m-d-y H:i', '11-29-73 23:01')
        );
        $this->assertEquals(
            '01-02-2001',
            $date->convertToDisplayDate('m-d-y', '01-02-01')
        );
        $this->assertEquals(
            '01-02-2001',
            $date->convertToDisplayDate('m-d-y', '01-02-2001')
        );
        $this->assertEquals(
            '01-02-2001',
            $date->convertToDisplayDate('m-d-y H:i', '01-02-01 05:11')
        );
        $this->assertEquals(
            '05:11',
            $date->convertToDisplayTime('m-d-y H:i', '01-02-01 05:11')
        );
        $this->assertEquals(
            '01-02-2001',
            $date->convertToDisplayDate('Y-m-d', '2001-01-02')
        );
        $this->assertEquals(
            '01-02-2001',
            $date->convertToDisplayDate('Y-m-d H:i', '2001-01-02 05:11')
        );
        $this->assertEquals(
            '05:11',
            $date->convertToDisplayTime('Y-m-d H:i', '2001-01-02 05:11')
        );
        $this->assertEquals(
            '01-2001',
            $date->convertFromDisplayDate('m-Y', '01-02-2001')
        );
        $usDateTime = new \DateTime('now', new \DateTimeZone('America/New_York'));
        $this->assertEquals(
            $usDateTime->format('Y-m-d H:i:s'),
            $date->convert('U', 'Y-m-d H:i:s', $usDateTime->getTimestamp())
        );
        $dateTime = $date->convertToDateTime('U', $usDateTime->getTimestamp());
        $this->assertInstanceOf(\DateTime::class, $dateTime);
        $this->assertEquals('America/New_York', $dateTime->getTimezone()->getName());
        $this->assertEquals('America/New_York', $date->getTimeZone()->getName());

        // Check for proper handling of known problems:
        try {
            $bad = $date->convertToDisplayDate('U', 'invalid');
            $this->fail('Expected exception did not occur');
        } catch (DateException $e) {
            $this->assertTrue(
                (bool)stristr($e->getMessage(), 'failed to parse time string')
            );
        }
        try {
            $bad = $date->convertToDisplayDate('d-m-Y', '31-02-2001');
            $this->fail('Expected exception did not occur');
        } catch (DateException $e) {
            $this->assertTrue(
                (bool)stristr($e->getMessage(), 'parsed date was invalid')
            );
        }

        // Try an object with a different configuration
        $options = [
            'displayDateFormat' => 'd-m-Y',
            'displayTimeFormat' => 'H:i:s',
            'timezone' => 'Europe/Helsinki'
        ];
        $date2 = new Converter($options);
        $this->assertEquals(
            '29-11-1973--23:34:39',
            $date2->convertToDisplayDateAndTime('U', 123456879, '--')
        );
        $this->assertEquals('Europe/Helsinki', $date2->getTimeZone()->getName());
    }
}
