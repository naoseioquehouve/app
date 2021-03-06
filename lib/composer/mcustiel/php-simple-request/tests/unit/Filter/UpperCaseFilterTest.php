<?php
/**
 * This file is part of php-simple-request.
 *
 * php-simple-request is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * php-simple-request is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with php-simple-request.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace Unit\Filter;

use Mcustiel\SimpleRequest\Filter\UpperCase;

class UpperCaseFilterTest extends \PHPUnit_Framework_TestCase
{
    const EXPECTED = 'TEST';
    private $filter;

    public function setUp()
    {
        $this->filter = new UpperCase();
        $this->filter->setSpecification();
    }

    public function testFilter()
    {
        $this->assertEquals(self::EXPECTED, $this->filter->filter('Test'));
        $this->assertEquals(self::EXPECTED, $this->filter->filter(self::EXPECTED));
        $this->assertEquals(self::EXPECTED, $this->filter->filter('test'));
    }
}
