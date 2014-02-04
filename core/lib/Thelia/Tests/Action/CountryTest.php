<?php
/*************************************************************************************/
/*                                                                                   */
/*      Thelia	                                                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : info@thelia.net                                                      */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      This program is free software; you can redistribute it and/or modify         */
/*      it under the terms of the GNU General Public License as published by         */
/*      the Free Software Foundation; either version 3 of the License                */
/*                                                                                   */
/*      This program is distributed in the hope that it will be useful,              */
/*      but WITHOUT ANY WARRANTY; without even the implied warranty of               */
/*      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                */
/*      GNU General Public License for more details.                                 */
/*                                                                                   */
/*      You should have received a copy of the GNU General Public License            */
/*	    along with this program. If not, see <http://www.gnu.org/licenses/>.         */
/*                                                                                   */
/*************************************************************************************/

namespace Thelia\Tests\Action;

use Thelia\Action\Country;
use Thelia\Core\Event\Country\CountryCreateEvent;
use Thelia\Core\Event\Country\CountryDeleteEvent;
use Thelia\Core\Event\Country\CountryToggleDefaultEvent;
use Thelia\Core\Event\Country\CountryUpdateEvent;
use Thelia\Model\Country as CountryModel;
use Thelia\Model\CountryQuery;


/**
 * Class CountryTest
 * @package Thelia\Tests\Action
 * @author Manuel Raynaud <mraynaud@openstudio.fr>
 */
class CountryTest extends \PHPUnit_Framework_TestCase
{
    protected  $dispatcher;

    public function setUp()
    {
        $this->dispatcher = $this->getMock("Symfony\Component\EventDispatcher\EventDispatcherInterface");
    }


    public function testCreate()
    {
        $event = new CountryCreateEvent();

        $event
            ->setIsocode('001')
            ->setIsoAlpha2('AA')
            ->setIsoAlpha3('AAA')
            ->setLocale('en_US')
            ->setTitle('Test')
            ->setDispatcher($this->dispatcher)
        ;

        $action = new Country();
        $action->create($event);

        $createdCountry = $event->getCountry();

        $this->assertInstanceOf('Thelia\Model\Country', $createdCountry);
        $this->assertFalse($createdCountry->isNew());

        $this->assertEquals('001', $createdCountry->getIsocode());
        $this->assertEquals('AA', $createdCountry->getIsoalpha2());
        $this->assertEquals('AAA', $createdCountry->getIsoalpha3());
        $this->assertEquals('en_US', $createdCountry->getLocale());
        $this->assertEquals('Test', $createdCountry->getTitle());

        return $createdCountry;
    }

    /**
     * @param CountryModel $country
     * @depends testCreate
     */
    public function testUpdate(CountryModel $country)
    {
        $event = new CountryUpdateEvent($country->getId());

        $event
            ->setIsocode('002')
            ->setIsoAlpha2('BB')
            ->setIsoAlpha3('BBB')
            ->setLocale('en_US')
            ->setTitle('Test')
            ->setDispatcher($this->dispatcher)
        ;

        $action = new Country();
        $action->update($event);

        $updatedCountry = $event->getCountry();

        $this->assertInstanceOf('Thelia\Model\Country', $updatedCountry);

        $this->assertEquals('002', $updatedCountry->getIsocode());
        $this->assertEquals('BB', $updatedCountry->getIsoalpha2());
        $this->assertEquals('BBB', $updatedCountry->getIsoalpha3());
        $this->assertEquals('en_US', $updatedCountry->getLocale());
        $this->assertEquals('Test', $updatedCountry->getTitle());

        return $updatedCountry;
    }

    /**
     * @param CountryModel $country
     * @depends testUpdate
     */
    public function testDelete(CountryModel $country)
    {
        $event = new CountryDeleteEvent($country->getId());
        $event->setDispatcher($this->dispatcher);

        $action = new Country();
        $action->delete($event);

        $deletedCountry = $event->getCountry();

        $this->assertInstanceOf('Thelia\Model\Country', $deletedCountry);
        $this->assertTrue($deletedCountry->isDeleted());
    }

    public function testToggleDefault()
    {
        $country = CountryQuery::create()
            ->filterByByDefault(0)
            ->addAscendingOrderByColumn('RAND()')
            ->findOne();

        $event = new CountryToggleDefaultEvent($country->getId());
        $event->setDispatcher($this->dispatcher);

        $action = new Country();
        $action->toggleDefault($event);

        $updatedCountry = $event->getCountry();

        $this->assertInstanceOf('Thelia\Model\Country', $updatedCountry);
        $this->assertEquals(1, $updatedCountry->getByDefault());

        $this->assertEquals(1, CountryQuery::create()->filterByByDefault(1)->count());
    }
}