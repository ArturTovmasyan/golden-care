<?php

namespace App\Util\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class FormatDayExtension
 * @package App\Util\Twig\Extension
 */
class FormatDayExtension extends AbstractExtension
{
    /**
     * @return array
     */
    public function getFunctions(): array
    {
        return array(
            new TwigFunction('formatDay', array($this, 'formatDay')),
        );
    }

    public function formatDay($days): string
    {
        $year = (int)floor($days / 365);
        $month = (int)floor(($days - ((int)($days / 365) * 365)) / 30);
        $day = (int)floor($days - (($year * 365) + ($month * 30)));

        $total = $day.' Days';

        if ($day > 0 && $day === 1) {
            $total = $day.' Day';
        }

        if ($day > 0 && $day > 1) {
            $total = $day.' Days';
        }

        if ($month > 0) {
            $totalDay = '';
            if ($day > 0  && $day === 1) {
                $totalDay = ', '.$day.' Day';
            }

            if ($day > 0  && $day > 1) {
                $totalDay = ', '.$day.' Days';
            }

            if ($month > 1) {
                $total = $month.' Months'.$totalDay;
            } else {
                $total = $month.' Month'.$totalDay;
            }
        }

        if ($year > 0) {
            $totalDay = '';
            if ($day > 0  && $day === 1) {
                $totalDay = ', '.$day.' Day';
            }

            if ($day > 0  && $day > 1) {
                $totalDay = ', '.$day.' Days';
            }

            $totalMonth = '';
            if ($month > 0  && $month === 1) {
                $totalMonth = ', '.$month.' Month';
            }

            if ($month > 0  && $month > 1) {
                $totalMonth = ', '.$month.' Months';
            }

            if ($year > 1) {
                $total = $year.' Years'.$totalMonth.$totalDay;
            } else {
                $total = $year.' Year'.$totalMonth.$totalDay;
            }
        }

        return $total;
    }
}