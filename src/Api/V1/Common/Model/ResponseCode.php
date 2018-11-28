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
    const VALIDATION_ERROR_EXCEPTION                             = 610;
    const ROLE_NOT_FOUND_EXCEPTION                               = 611;
    const SPACE_NOT_FOUND_EXCEPTION                              = 612;
    const USER_NOT_FOUND_EXCEPTION                               = 613;
    const USER_ALREADY_JOINED_EXCEPTION                          = 614;
    const SPACE_HAVE_NOT_DEFAULT_ROLE_EXCEPTION                  = 615;
    const INVALID_USER_ACCESS_TO_SPACE                           = 616;
    const INVALID_USER_CONFIRMATION_TOKEN                        = 617;
    const DUPLICATE_USER_EXCEPTION                               = 618;
    const NEW_PASSWORD_MUST_BE_DIFFERENT_EXCEPTION               = 619;
    const INVALID_PASSWORD_EXCEPTION                             = 620;
    const USER_WITHOUT_ROLE_EXCEPTION                            = 621;
    const SPACE_HAVE_NOT_ACCESS_TO_ROLE_EXCEPTION                = 622;
    const INVALID_CONFIRMATION_TOKEN_EXCEPTION                   = 623;
    const SALUTATION_NOT_FOUND_EXCEPTION                         = 624;
    const CARE_LEVEL_NOT_FOUND_EXCEPTION                         = 625;
    const CITY_STATE_ZIP_NOT_FOUND_EXCEPTION                     = 626;
    const RELATIONSHIP_NOT_FOUND_EXCEPTION                       = 627;
    const MEDICATION_NOT_FOUND_EXCEPTION                         = 628;
    const DIET_NOT_FOUND_EXCEPTION                               = 629;
    const DIAGNOSIS_NOT_FOUND_EXCEPTION                          = 630;
    const PHYSICIAN_NOT_FOUND_EXCEPTION                          = 631;
    const SPACE_HAVE_NOT_ACCESS_TO_PHYSICIAN_EXCEPTION           = 632;
    const ALLERGEN_NOT_FOUND_EXCEPTION                           = 633;
    const MEDICAL_HISTORY_CONDITION_NOT_FOUND_EXCEPTION          = 634;
    const MEDICATION_FORM_FACTOR_NOT_FOUND_EXCEPTION             = 635;
    const FACILITY_NOT_FOUND_EXCEPTION                           = 636;
    const APARTMENT_NOT_FOUND_EXCEPTION                          = 637;
    const REGION_NOT_FOUND_EXCEPTION                             = 638;
    const DINING_ROOM_NOT_FOUND_EXCEPTION                        = 639;
    const RESIDENT_NOT_FOUND_EXCEPTION                           = 640;
    const FACILITY_ROOM_NOT_FOUND_EXCEPTION                      = 641;
    const APARTMENT_ROOM_NOT_FOUND_EXCEPTION                     = 642;
    const RESIDENT_DIET_NOT_FOUND_EXCEPTION                      = 643;
    const RESIDENT_MEDICATION_NOT_FOUND_EXCEPTION                = 644;
    const FILE_SYSTEM_EXCEPTION                                  = 645;
    const FOLDER_NOT_DEFINED_EXCEPTION                           = 646;
    const FILE_EXTENSION_NOT_SUPPORTED                           = 647;
    const SPECIALITY_NOT_FOUND_EXCEPTION                         = 648;
    const RESIDENT_MEDICATION_ALLERGY_NOT_FOUND_EXCEPTION        = 649;
    const PHONE_SINGLE_PRIMARY_EXCEPTION                         = 650;
    const MEDICATION_NOT_SINGLE_EXCEPTION                        = 651;
    const RESIDENT_ALLERGEN_NOT_FOUND_EXCEPTION                  = 652;
    const ALLERGEN_NOT_SINGLE_EXCEPTION                          = 653;
    const RESIDENT_MEDICAL_HISTORY_CONDITION_NOT_FOUND_EXCEPTION = 654;
    const MEDICAL_HISTORY_CONDITION_NOT_SINGLE_EXCEPTION         = 655;
    const RESPONSIBLE_PERSON_NOT_FOUND_EXCEPTION                 = 656;
    const RESIDENT_DIAGNOSIS_NOT_FOUND_EXCEPTION                 = 657;
    const DIAGNOSIS_NOT_SINGLE_EXCEPTION                         = 658;
    const RESIDENT_RESPONSIBLE_PERSON_NOT_FOUND_EXCEPTION        = 659;
    const RESIDENT_PHYSICIAN_NOT_FOUND_EXCEPTION                 = 660;
    const RESIDENT_HAVE_PRIMARY_PHYSICIAN_EXCEPTION              = 661;
    const RESIDENT_PHYSICIAN_SPECIALITY_EXCEPTION_NOT_FOUND      = 662;
    const PHYSICIAN_SPECIALITY_DUPLICATE_REQUEST_EXCEPTION       = 663;
    const PAYMENT_SOURCE_NOT_FOUND_EXCEPTION                     = 664;
    const ASSESSMENT_CATEGORY_NOT_FOUND_EXCEPTION                = 665;

    /**
     * @var array
     */
    public static $titles = [
        // success
        self::RECOVERY_LINK_SENT_TO_EMAIL                            => ['httpCode' => Response::HTTP_CREATED, 'message' => 'Password recovery link sent, please check email'],
        self::INVITATION_LINK_SENT_TO_EMAIL                          => ['httpCode' => Response::HTTP_CREATED, 'message' => 'Invitation sent to email address, please check email'],
        // errors
        self::VALIDATION_ERROR_EXCEPTION                             => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Validation error'],
        self::ROLE_NOT_FOUND_EXCEPTION                               => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Role not found'],
        self::SPACE_NOT_FOUND_EXCEPTION                              => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Space not found'],
        self::USER_NOT_FOUND_EXCEPTION                               => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'User not found'],
        self::USER_ALREADY_JOINED_EXCEPTION                          => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'User already joined to space'],
        self::SPACE_HAVE_NOT_DEFAULT_ROLE_EXCEPTION                  => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Space haven\'t default role'],
        self::INVALID_USER_ACCESS_TO_SPACE                           => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Invalid user access for space'],
        self::INVALID_USER_CONFIRMATION_TOKEN                        => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'User haven\'t completed account, please check email for confirmation account'],
        self::DUPLICATE_USER_EXCEPTION                               => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'User with this email address or username already exist'],
        self::NEW_PASSWORD_MUST_BE_DIFFERENT_EXCEPTION               => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'New password must be different from last password'],
        self::INVALID_PASSWORD_EXCEPTION                             => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Old password is not valid'],
        self::USER_WITHOUT_ROLE_EXCEPTION                            => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Change users related role before removal'],
        self::SPACE_HAVE_NOT_ACCESS_TO_ROLE_EXCEPTION                => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Space haven\'t access to role'],
        self::INVALID_CONFIRMATION_TOKEN_EXCEPTION                   => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Invalid confirmation token'],
        self::SALUTATION_NOT_FOUND_EXCEPTION                         => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Salutation not found'],
        self::CARE_LEVEL_NOT_FOUND_EXCEPTION                         => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'CareLevel not found'],
        self::CITY_STATE_ZIP_NOT_FOUND_EXCEPTION                     => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'CityStateZip not found'],
        self::RELATIONSHIP_NOT_FOUND_EXCEPTION                       => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Relationship not found'],
        self::MEDICATION_NOT_FOUND_EXCEPTION                         => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Medication not found'],
        self::DIET_NOT_FOUND_EXCEPTION                               => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Diet not found'],
        self::DIAGNOSIS_NOT_FOUND_EXCEPTION                          => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Diagnosis not found'],
        self::PHYSICIAN_NOT_FOUND_EXCEPTION                          => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Physician not found'],
        self::SPACE_HAVE_NOT_ACCESS_TO_PHYSICIAN_EXCEPTION           => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Space haven\'t access to physician'],
        self::ALLERGEN_NOT_FOUND_EXCEPTION                           => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Allergen not found'],
        self::MEDICAL_HISTORY_CONDITION_NOT_FOUND_EXCEPTION          => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'MedicalHistoryCondition not found'],
        self::MEDICATION_FORM_FACTOR_NOT_FOUND_EXCEPTION             => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'MedicationFormFactor not found'],
        self::FACILITY_NOT_FOUND_EXCEPTION                           => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Facility not found'],
        self::APARTMENT_NOT_FOUND_EXCEPTION                          => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Apartment not found'],
        self::REGION_NOT_FOUND_EXCEPTION                             => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Region not found'],
        self::DINING_ROOM_NOT_FOUND_EXCEPTION                        => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'DiningRoom not found'],
        self::RESIDENT_NOT_FOUND_EXCEPTION                           => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Resident not found'],
        self::FACILITY_ROOM_NOT_FOUND_EXCEPTION                      => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'FacilityRoom not found'],
        self::APARTMENT_ROOM_NOT_FOUND_EXCEPTION                     => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'ApartmentRoom not found'],
        self::RESIDENT_DIET_NOT_FOUND_EXCEPTION                      => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'ResidentDiet not found'],
        self::RESIDENT_MEDICATION_NOT_FOUND_EXCEPTION                => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'ResidentMedication not found'],
        self::FILE_SYSTEM_EXCEPTION                                  => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'File system not responded'],
        self::FOLDER_NOT_DEFINED_EXCEPTION                           => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'File system folder not defined or not writable'],
        self::FILE_EXTENSION_NOT_SUPPORTED                           => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'File extension not supported'],
        self::SPECIALITY_NOT_FOUND_EXCEPTION                         => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Speciality not found'],
        self::RESIDENT_MEDICATION_ALLERGY_NOT_FOUND_EXCEPTION        => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'ResidentMedicationAllergy not found'],
        self::PHONE_SINGLE_PRIMARY_EXCEPTION                         => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Duplicate primary phone number'],
        self::MEDICATION_NOT_SINGLE_EXCEPTION                        => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Medication not single'],
        self::RESIDENT_ALLERGEN_NOT_FOUND_EXCEPTION                  => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'ResidentAllergen not found'],
        self::ALLERGEN_NOT_SINGLE_EXCEPTION                          => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Allergen not single'],
        self::RESIDENT_MEDICAL_HISTORY_CONDITION_NOT_FOUND_EXCEPTION => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'ResidentMedicalHistoryCondition not found'],
        self::MEDICAL_HISTORY_CONDITION_NOT_SINGLE_EXCEPTION         => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'MedicalHistoryCondition not single'],
        self::RESPONSIBLE_PERSON_NOT_FOUND_EXCEPTION                 => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Responsible person not found'],
        self::RESIDENT_DIAGNOSIS_NOT_FOUND_EXCEPTION                 => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'ResidentDiagnosis not found'],
        self::DIAGNOSIS_NOT_SINGLE_EXCEPTION                         => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Diagnosis not single'],
        self::RESIDENT_RESPONSIBLE_PERSON_NOT_FOUND_EXCEPTION        => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'ResidentResponsiblePerson not found'],
        self::RESIDENT_PHYSICIAN_NOT_FOUND_EXCEPTION                 => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'ResidentPhysician not found'],
        self::RESIDENT_HAVE_PRIMARY_PHYSICIAN_EXCEPTION              => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Resident already have primary physician'],
        self::RESIDENT_PHYSICIAN_SPECIALITY_EXCEPTION_NOT_FOUND      => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Resident physician speciality not found'],
        self::PHYSICIAN_SPECIALITY_DUPLICATE_REQUEST_EXCEPTION       => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Speciality duplicate request'],
        self::PAYMENT_SOURCE_NOT_FOUND_EXCEPTION                     => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'PaymentSource not found'],
        self::ASSESSMENT_CATEGORY_NOT_FOUND_EXCEPTION                => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Assessment category not found'],
    ];
}