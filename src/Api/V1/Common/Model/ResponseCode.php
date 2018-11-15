<?php
namespace App\Api\V1\Common\Model;

use Symfony\Component\HttpFoundation\Response;

class ResponseCode
{
    /**
     * Success codes
     */
    const RECOVERY_LINK_SENT_TO_EMAIL   = 230;
    const INVITATION_LINK_SENT_TO_EMAIL = 231;

    /**
     * Error codes
     */
    const VALIDATION_ERROR_EXCEPTION                    = 610;
    const ROLE_NOT_FOUND_EXCEPTION                      = 611;
    const SPACE_NOT_FOUND_EXCEPTION                     = 612;
    const USER_NOT_FOUND_EXCEPTION                      = 613;
    const USER_ALREADY_JOINED_EXCEPTION                 = 614;
    const SPACE_HAVE_NOT_DEFAULT_ROLE_EXCEPTION         = 615;
    const INVALID_USER_ACCESS_TO_SPACE                  = 616;
    const INVALID_USER_CONFIRMATION_TOKEN               = 617;
    const DUPLICATE_USER_EXCEPTION                      = 618;
    const NEW_PASSWORD_MUST_BE_DIFFERENT_EXCEPTION      = 619;
    const INVALID_PASSWORD_EXCEPTION                    = 620;
    const USER_WITHOUT_ROLE_EXCEPTION                   = 621;
    const SPACE_HAVE_NOT_ACCESS_TO_ROLE_EXCEPTION       = 622;
    const INVALID_CONFIRMATION_TOKEN_EXCEPTION          = 623;
    const SALUTATION_NOT_FOUND_EXCEPTION                = 624;
    const CARE_LEVEL_NOT_FOUND_EXCEPTION                = 625;
    const CITY_STATE_ZIP_NOT_FOUND_EXCEPTION            = 626;
    const RELATIONSHIP_NOT_FOUND_EXCEPTION              = 627;
    const MEDICATION_NOT_FOUND_EXCEPTION                = 628;
    const DIET_NOT_FOUND_EXCEPTION                      = 629;
    const DIAGNOSIS_NOT_FOUND_EXCEPTION                 = 630;
    const PHYSICIAN_NOT_FOUND_EXCEPTION                 = 631;
    const SPACE_HAVE_NOT_ACCESS_TO_PHYSICIAN_EXCEPTION  = 632;
    const ALLERGEN_NOT_FOUND_EXCEPTION                  = 633;

    /**
     * @var array
     */
    public static $titles = [
        // success
        self::RECOVERY_LINK_SENT_TO_EMAIL               => ['httpCode' => Response::HTTP_CREATED, 'message' => 'Password recovery link sent, please check email'],
        self::INVITATION_LINK_SENT_TO_EMAIL             => ['httpCode' => Response::HTTP_CREATED, 'message' => 'Invitation sent to email address, please check email'],
        // errors
        self::VALIDATION_ERROR_EXCEPTION                => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Validation error'],
        self::ROLE_NOT_FOUND_EXCEPTION                  => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Role not found'],
        self::SPACE_NOT_FOUND_EXCEPTION                 => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Space not found'],
        self::USER_NOT_FOUND_EXCEPTION                  => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'User not found'],
        self::USER_ALREADY_JOINED_EXCEPTION             => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'User already joined to space'],
        self::SPACE_HAVE_NOT_DEFAULT_ROLE_EXCEPTION     => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Space haven\'t default role'],
        self::INVALID_USER_ACCESS_TO_SPACE              => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Invalid user access for space'],
        self::INVALID_USER_CONFIRMATION_TOKEN           => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'User haven\'t completed account, please check email for confirmation account'],
        self::DUPLICATE_USER_EXCEPTION                  => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'User with this email address or username already exist'],
        self::NEW_PASSWORD_MUST_BE_DIFFERENT_EXCEPTION  => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'New password must be different from last password'],
        self::INVALID_PASSWORD_EXCEPTION                => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Old password is not valid'],
        self::USER_WITHOUT_ROLE_EXCEPTION               => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Change users related role before removal'],
        self::SPACE_HAVE_NOT_ACCESS_TO_ROLE_EXCEPTION   => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Space haven\'t access to role'],
        self::INVALID_CONFIRMATION_TOKEN_EXCEPTION      => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Invalid confirmation token'],
        self::SALUTATION_NOT_FOUND_EXCEPTION            => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Salutation not found'],
        self::CARE_LEVEL_NOT_FOUND_EXCEPTION            => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'CareLevel not found'],
        self::CITY_STATE_ZIP_NOT_FOUND_EXCEPTION        => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'CityStateZip not found'],
        self::RELATIONSHIP_NOT_FOUND_EXCEPTION          => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Relationship not found'],
        self::MEDICATION_NOT_FOUND_EXCEPTION            => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Medication not found'],
        self::DIET_NOT_FOUND_EXCEPTION                  => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Diet not found'],
        self::DIAGNOSIS_NOT_FOUND_EXCEPTION             => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Diagnosis not found'],
        self::PHYSICIAN_NOT_FOUND_EXCEPTION             => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Physician not found'],
        self::SPACE_HAVE_NOT_ACCESS_TO_PHYSICIAN_EXCEPTION => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Space haven\'t access to physician'],
        self::ALLERGEN_NOT_FOUND_EXCEPTION              => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Allergen not found'],
    ];
}