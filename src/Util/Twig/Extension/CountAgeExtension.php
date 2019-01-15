<?php

namespace App\Util\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class CountAgeExtension
 * @package App\Util\Twig\Extension
 */
class CountAgeExtension extends AbstractExtension
{
    /**
     * @return array
     */
    public function getFunctions(): array
    {
        return array(
            new TwigFunction('age', array($this, 'ageCount')),
        );
    }

    public function ageCount($birthDate)
    {
        $newDate = date('m/d/Y', strtotime($birthDate));

        //explode the date to get month, day and year
        $newDate = explode('/', $newDate);
        //get age from date or birthDate
        $age = (date('md', date('U', mktime(0, 0, 0, $newDate[0], $newDate[1], $newDate[2]))) > date('md')
            ? ((date('Y') - $newDate[2]) - 1)
            : (date('Y') - $newDate[2]));

        return $age;
    }
}