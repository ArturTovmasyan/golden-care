<?php

namespace App\Util\Twig\Extension;

use App\Model\AdmissionType;
use App\Model\GroupType;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class AdmissionTypeExtension
 * @package App\Util\Twig\Extension
 */
class AdmissionTypeExtension extends AbstractExtension
{
    /**
     * @return array
     */
    public function getFunctions(): array
    {
        return array(
            new TwigFunction('admissionType', array($this, 'admissionType')),
        );
    }

    public function admissionType($groupType, $admissionType)
    {
        if ((int)$groupType === GroupType::TYPE_APARTMENT) {
            return AdmissionType::getApartmentTypes()[$admissionType];
        }

        return AdmissionType::getTypes()[$admissionType];
    }
}