<?php

/**
 * ReactPHP Symfony Bridge.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/reactphp/symfony Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\ReactPHPBundle\Service;

/**
 * Class DatesService, Service to retrieve the current date. For test, it's able to register an specific date to return
 * at each coll of getNow().
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/symfony-react Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class DatesService
{
    /**
     * Instance set by setNow() to return at getNow() instead of new \DateTime instance.
     *
     * @var \DateTime
     */
    private $date = null;

    /**
     * Return a clone a the defined date by setNow(), or a new instance of \DateTime representing current time.
     *
     * @return \DateTime
     */
    public function getNow(): \DateTime
    {
        if ($this->date instanceof \DateTime) {
            return clone $this->date;
        }

        return new \DateTime();
    }

    /**
     * To define a specific \DateTime instance to use at getNow().
     *
     * @param \DateTime $date
     *
     * @return DatesService
     */
    public function setNow(\DateTime $date): DatesService
    {
        $this->date = $date;

        return $this;
    }
}
