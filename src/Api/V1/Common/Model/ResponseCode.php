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
    const RECOVERY_LINK_INVALID         = 232;
    const ACTIVATION_LINK_INVALID       = 233;
    const USER_BLOCKED_EXCEPTION        = 234;

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
    const ASSESSMENT_CARE_LEVEL_GROUP_NOT_FOUND_EXCEPTION        = 666;
    const RESIDENT_RENT_NOT_FOUND_EXCEPTION                      = 667;
    const RESIDENT_RENT_NEGATIVE_REMAINING_TOTAL                 = 668;
    const ASSESSMENT_CARE_LEVEL_NOT_FOUND_EXCEPTION              = 669;
    const RESPONSIBLE_PERSON_ROLE_NOT_FOUND_EXCEPTION            = 670;
    const DATES_OVERLAP_EXCEPTION                                = 671;
    const MISSING_BASE_RATE_FOR_CARE_LEVEL                       = 672;
    const START_GREATER_VALID_THROUGH_DATE_EXCEPTION             = 673;
    const INVALID_DISCHARGE_DATE_EXCEPTION                       = 674;
    const NAME_NOT_BE_BLANK_EXCEPTION                            = 675;
    const GRID_OPTIONS_NOT_FOUND_EXCEPTION                       = 680;
    const ASSESSMENT_FORM_NOT_FOUND_EXCEPTION                    = 681;
    const EVENT_DEFINITION_NOT_FOUND_EXCEPTION                   = 682;
    const ASSESSMENT_NOT_FOUND_EXCEPTION                         = 683;
    const ASSESSMENT_CATEGORY_MULTIPLE_EXCEPTION                 = 684;
    const ASSESSMENT_ROW_NOT_AVAILABLE_EXCEPTION                 = 685;
    const PHYSICIAN_NOT_BE_BLANK_EXCEPTION                       = 686;
    const RESPONSIBLE_PERSON_NOT_BE_BLANK_EXCEPTION              = 687;
    const ADDITIONAL_DATE_NOT_BE_BLANK_EXCEPTION                 = 688;
    const RESIDENT_EVENT_NOT_FOUND_EXCEPTION                     = 689;
    const REPORT_NOT_FOUND_EXCEPTION                             = 690;
    const REPORT_FORMAT_NOT_FOUND_EXCEPTION                      = 691;
    const REPORT_MISCONFIGURATION_EXCEPTION                      = 692;
    const FACILITY_BED_NOT_FOUND_EXCEPTION                       = 693;
    const APARTMENT_BED_NOT_FOUND_EXCEPTION                      = 694;
    const ASSESSMENT_ROW_NOT_FOUND_EXCEPTION                     = 695;
    const START_GREATER_END_DATE_EXCEPTION                       = 697;
    const END_DATE_NOT_BE_BLANK_EXCEPTION                        = 698;
    const INCORRECT_STRATEGY_TYPE_EXCEPTION                      = 699;
    const CAN_NOT_REMOVE_BED_EXCEPTION                           = 700;
    const LEAD_CONTACT_ORGANIZATION_CHANGED_EXCEPTION            = 701;
    const REGION_CAN_NOT_HAVE_BED_EXCEPTION                      = 702;
    const UNHANDLED_RENT_PERIOD_EXCEPTION                        = 703;
    const RESIDENT_ADMISSION_NOT_FOUND_EXCEPTION                 = 704;
    const USER_ALREADY_INVITED_EXCEPTION                         = 705;
    const USER_NOT_YET_INVITED_EXCEPTION                         = 706;
    const SPACE_ALREADY_HAS_OWNER_EXCEPTION                      = 707;
    const LEAD_CARE_TYPE_NOT_FOUND_EXCEPTION                     = 708;
    const LEAD_OUTREACH_TYPE_NOT_FOUND_EXCEPTION                 = 709;
    const LEAD_ACTIVITY_STATUS_NOT_FOUND_EXCEPTION               = 710;
    const LEAD_ACTIVITY_TYPE_NOT_FOUND_EXCEPTION                 = 711;
    const LEAD_REFERRER_TYPE_NOT_FOUND_EXCEPTION                 = 712;
    const LEAD_ORGANIZATION_NOT_FOUND_EXCEPTION                  = 713;
    const LEAD_REFERRAL_NOT_FOUND_EXCEPTION                      = 714;
    const LEAD_ACTIVITY_NOT_FOUND_EXCEPTION                      = 715;
    const INCORRECT_LEAD_OWNER_TYPE_EXCEPTION                    = 716;
    const LEAD_LEAD_NOT_FOUND_EXCEPTION                          = 717;
    const LEAD_RP_PHONE_OR_EMAIL_NOT_BE_BLANK_EXCEPTION          = 718;
    const RESIDENT_ADMISSION_ONLY_ADMIT_EXCEPTION                = 719;
    const LAST_RESIDENT_ADMISSION_NOT_FOUND_EXCEPTION            = 720;
    const RESIDENT_ADMISSION_TWO_TIME_A_ROW_EXCEPTION            = 721;
    const RESIDENT_ADMISSION_ONLY_READMIT_EXCEPTION              = 722;
    const NOTIFICATION_TYPE_NOT_FOUND_EXCEPTION                  = 723;
    const NOTIFICATION_NOT_FOUND_EXCEPTION                       = 724;
    const CHANGE_LOG_NOT_FOUND_EXCEPTION                         = 725;
    const INCORRECT_CHANGE_LOG_TYPE_EXCEPTION                    = 726;
    const DINING_ROOM_NOT_VALID_EXCEPTION                        = 727;
    const LEAD_ALREADY_JOINED_IN_REFERRAL_EXCEPTION              = 728;
    const INSURANCE_COMPANY_NOT_FOUND_EXCEPTION                  = 729;
    const RESIDENT_HEALTH_INSURANCE_NOT_FOUND_EXCEPTION          = 730;
    const TIME_SPAN_IS_GREATHER_THAN_12_MONTHS_EXCEPTION         = 731;
    const DOCUMENT_NOT_FOUND_EXCEPTION                           = 732;
    const RESIDENT_DOCUMENT_NOT_FOUND_EXCEPTION                  = 733;
    const RESIDENT_ADMIT_ONLY_ONE_TIME_EXCEPTION                 = 734;
    const INVALID_EFFECTIVE_DATE_EXCEPTION                       = 735;
    const LEAD_CONTACT_NOT_FOUND_EXCEPTION                       = 736;
    const INCORRECT_RESIDENT_STATE_EXCEPTION                     = 737;
    const LEAD_STAGE_CHANGE_REASON_NOT_FOUND_EXCEPTION           = 738;
    const LEAD_FUNNEL_STAGE_NOT_FOUND_EXCEPTION                  = 739;
    const LEAD_TEMPERATURE_NOT_FOUND_EXCEPTION                   = 740;
    const LEAD_LEAD_FUNNEL_STAGE_NOT_FOUND_EXCEPTION             = 741;
    const LEAD_LEAD_TEMPERATURE_NOT_FOUND_EXCEPTION              = 742;
    const INCOMPLETE_CHUNK_DATA_EXCEPTION                        = 743;
    const DUPLICATE_IMAGE_BY_REQUEST_ID_EXCEPTION                = 744;
    const LEAD_OUTREACH_NOT_FOUND_EXCEPTION                      = 745;
    const LEAD_CONTACT_ORGANIZATION_NOT_ALLOWED_CHANGE_EXCEPTION = 746;
    const LEAD_INCORRECT_ACTIVITY_TYPE_EXCEPTION                 = 747;
    const DOCUMENT_CATEGORY_NOT_FOUND_EXCEPTION                  = 748;
    const FACILITY_DASHBOARD_NOT_FOUND_EXCEPTION                 = 749;
    const FACILITY_DOCUMENT_NOT_FOUND_EXCEPTION                  = 750;
    const RESIDENT_RENT_INCREASE_NOT_FOUND_EXCEPTION             = 751;
    const THE_NAME_IS_ALREADY_IN_USE_IN_THIS_SPACE_EXCEPTION     = 752;
    const FACILITY_EVENT_NOT_FOUND_EXCEPTION                     = 753;
    const USER_NOT_BE_BLANK_EXCEPTION                            = 754;
    const RESIDENT_NOT_BE_BLANK_EXCEPTION                        = 755;
    const NOT_A_VALID_CHOICE_EXCEPTION                           = 756;
    const CORPORATE_EVENT_USER_NOT_FOUND_EXCEPTION               = 757;
    const CORPORATE_EVENT_NOT_FOUND_EXCEPTION                    = 758;
    const DUPLICATE_RESIDENT_EXCEPTION                           = 759;
    const ASSESSMENT_TYPE_NOT_FOUND_EXCEPTION                    = 760;
    const ACTIVE_RESIDENT_EXIST_IN_BED_EXCEPTION                 = 761;
    const FACILITY_ROOM_TYPE_NOT_FOUND_EXCEPTION                 = 762;
    const BASE_RATE_NOT_FOUND_EXCEPTION                          = 763;
    const BASE_RATE_NOT_BE_BLANK_EXCEPTION                       = 764;
    const INVALID_PRIVATE_ROOM_EXCEPTION                         = 765;
    const INVALID_SHARED_ROOM_EXCEPTION                          = 766;
    const FACILITY_ROOM_TYPE_FACILITY_CHANGED_EXCEPTION          = 767;
    const RENT_REASON_NOT_FOUND_EXCEPTION                        = 768;
    const DUPLICATE_BASE_RATE_BY_DATE_EXCEPTION                  = 769;
    const PAYMENT_SOURCE_DUPLICATE_BASE_RATE_BY_DATE_EXCEPTION   = 770;
    const SUBJECT_NOT_BE_BLANK_EXCEPTION                         = 771;
    const CSV_REPORT_NOT_FOUND_EXCEPTION                         = 772;
    const CSV_REPORT_HASH_HAS_EXPIRED                            = 773;
    const FILE_EXTENSION_FOUND_EXCEPTION                         = 774;
    const ACTIVE_RESIDENT_EXIST_IN_ROOM_EXCEPTION                = 775;
    const LEAD_CURRENT_RESIDENCE_NOT_FOUND_EXCEPTION             = 776;
    const LEAD_HOBBY_NOT_FOUND_EXCEPTION                         = 777;
    const LEAD_QUALIFICATION_REQUIREMENT_NOT_FOUND_EXCEPTION     = 778;
    const RESIDENT_READMIT_ONLY_AFTER_DISCHARGE_EXCEPTION        = 779;
    const INVALID_BILL_THROUGH_DATE_EXCEPTION                    = 780;
    const RP_PAYMENT_TYPE_NOT_FOUND_EXCEPTION                    = 781;
    const EXPENSE_ITEM_NOT_FOUND_EXCEPTION                       = 782;
    const HOSPICE_PROVIDER_NOT_FOUND_EXCEPTION                   = 783;
    const HOSPICE_PROVIDER_NOT_BE_BLANK_EXCEPTION                = 784;
    const CREADIT_ITEM_NOT_FOUND_EXCEPTION                       = 785;
    const CAN_BE_CHANGED_IS_REQUIRED_EXCEPTION                   = 786;
    const LEAD_EMAIL_REVIEW_TYPE_NOT_FOUND_EXCEPTION             = 787;
    const LEAD_WEB_EMAIL_NOT_FOUND_EXCEPTION                     = 788;
    const RESIDENT_LEDGER_NOT_FOUND_EXCEPTION                    = 789;
    const RESIDENT_EXPENSE_ITEM_NOT_FOUND_EXCEPTION              = 790;
    const RESIDENT_CREADIT_ITEM_NOT_FOUND_EXCEPTION              = 791;
    const RESIDENT_PAYMENT_RECEIVED_ITEM_NOT_FOUND_EXCEPTION     = 792;
    const RESIDENT_AWAY_DAYS_NOT_FOUND_EXCEPTION                 = 793;
    const KEY_FINANCE_DATES_NOT_FOUND_EXCEPTION                  = 794;
    const DISCOUNT_ITEM_NOT_FOUND_EXCEPTION                      = 795;
    const RESIDENT_LEDGER_ALREADY_EXIST_EXCEPTION                = 796;
    const RESIDENT_DISCOUNT_ITEM_NOT_FOUND_EXCEPTION             = 797;
    const LATE_PAYMENT_NOT_FOUND_EXCEPTION                       = 798;
    const START_AND_END_DATE_NOT_SAME_MONTH_EXCEPTION            = 799;
    const INCORRECT_REPORT_PARAMETER                             = 800;
    const INVALID_GRANT_CONFIG                                   = 900;
    const DEFAULT_ROLE_NOT_FOUND_EXCEPTION                       = 901;
    const RESOURCE_NOT_FOUND_EXCEPTION                           = 902;
    const FEEDBACK_EXCEPTION                                     = 903;

    /**
     * @var array
     */
    public static $titles = [
        // success
        self::USER_BLOCKED_EXCEPTION                                 => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'The username "%s" blocked for 30 minutes from %s IP Address. Please, try again later...'],
        self::RECOVERY_LINK_SENT_TO_EMAIL                            => ['httpCode' => Response::HTTP_CREATED,     'message' => 'Password recovery link sent, please check your email.'],
        self::INVITATION_LINK_SENT_TO_EMAIL                          => ['httpCode' => Response::HTTP_CREATED,     'message' => 'Invitation sent to email address, please check your email.'],
        self::RECOVERY_LINK_INVALID                                  => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Recovery link invalid or expired.'],
        self::ACTIVATION_LINK_INVALID                                => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Activation link invalid or expired.'],
        // errors
        self::VALIDATION_ERROR_EXCEPTION                             => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Validation error.'],
        self::ROLE_NOT_FOUND_EXCEPTION                               => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Role not found.'],
        self::SPACE_NOT_FOUND_EXCEPTION                              => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Space not found.'],
        self::USER_NOT_FOUND_EXCEPTION                               => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'User not found.'],
        self::USER_ALREADY_JOINED_EXCEPTION                          => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'User already joined the space.'],
        self::SPACE_HAVE_NOT_DEFAULT_ROLE_EXCEPTION                  => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Space does not have a default role.'],
        self::INVALID_USER_ACCESS_TO_SPACE                           => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Invalid user access for the space.'],
        self::INVALID_USER_CONFIRMATION_TOKEN                        => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Incomplete account, please check email for account confirmation.'],
        self::DUPLICATE_USER_EXCEPTION                               => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'User with this email or username already exists.'],
        self::NEW_PASSWORD_MUST_BE_DIFFERENT_EXCEPTION               => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'New password must be different from the old password.'],
        self::INVALID_PASSWORD_EXCEPTION                             => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Invalid old password.'],
        self::USER_WITHOUT_ROLE_EXCEPTION                            => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Change user-related role before removal.'],
        self::SPACE_HAVE_NOT_ACCESS_TO_ROLE_EXCEPTION                => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Space does not have access to the role.'],
        self::INVALID_CONFIRMATION_TOKEN_EXCEPTION                   => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Invalid confirmation token.'],
        self::SALUTATION_NOT_FOUND_EXCEPTION                         => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Salutation not found.'],
        self::CARE_LEVEL_NOT_FOUND_EXCEPTION                         => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Care Level not found.'],
        self::CITY_STATE_ZIP_NOT_FOUND_EXCEPTION                     => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'City, State & Zip not found.'],
        self::RELATIONSHIP_NOT_FOUND_EXCEPTION                       => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Relationship not found.'],
        self::MEDICATION_NOT_FOUND_EXCEPTION                         => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Medication not found.'],
        self::DIET_NOT_FOUND_EXCEPTION                               => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Diet not found.'],
        self::DIAGNOSIS_NOT_FOUND_EXCEPTION                          => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Diagnosis not found.'],
        self::PHYSICIAN_NOT_FOUND_EXCEPTION                          => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Physician not found.'],
        self::SPACE_HAVE_NOT_ACCESS_TO_PHYSICIAN_EXCEPTION           => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Space does not have access to the physician.'],
        self::ALLERGEN_NOT_FOUND_EXCEPTION                           => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Allergen not found.'],
        self::MEDICAL_HISTORY_CONDITION_NOT_FOUND_EXCEPTION          => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Medical History Condition not found.'],
        self::MEDICATION_FORM_FACTOR_NOT_FOUND_EXCEPTION             => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Medication Form Factor not found.'],
        self::FACILITY_NOT_FOUND_EXCEPTION                           => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Facility not found.'],
        self::APARTMENT_NOT_FOUND_EXCEPTION                          => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Apartment not found.'],
        self::REGION_NOT_FOUND_EXCEPTION                             => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Region not found.'],
        self::DINING_ROOM_NOT_FOUND_EXCEPTION                        => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Dining Room not found.'],
        self::RESIDENT_NOT_FOUND_EXCEPTION                           => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Resident not found.'],
        self::FACILITY_ROOM_NOT_FOUND_EXCEPTION                      => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Facility Room not found.'],
        self::APARTMENT_ROOM_NOT_FOUND_EXCEPTION                     => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Apartment Room not found.'],
        self::RESIDENT_DIET_NOT_FOUND_EXCEPTION                      => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Resident Diet not found.'],
        self::RESIDENT_MEDICATION_NOT_FOUND_EXCEPTION                => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Resident Medication not found.'],
        self::FILE_SYSTEM_EXCEPTION                                  => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'File system did not respond.'],
        self::FOLDER_NOT_DEFINED_EXCEPTION                           => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'File system folder not defined or not writable.'],
        self::FILE_EXTENSION_NOT_SUPPORTED                           => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'File extension not supported.'],
        self::SPECIALITY_NOT_FOUND_EXCEPTION                         => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Speciality not found.'],
        self::RESIDENT_MEDICATION_ALLERGY_NOT_FOUND_EXCEPTION        => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Resident Medication Allergy not found.'],
        self::PHONE_SINGLE_PRIMARY_EXCEPTION                         => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Duplicate primary phone number.'],
        self::MEDICATION_NOT_SINGLE_EXCEPTION                        => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Medication not single.'],
        self::RESIDENT_ALLERGEN_NOT_FOUND_EXCEPTION                  => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Resident Allergen not found.'],
        self::ALLERGEN_NOT_SINGLE_EXCEPTION                          => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Allergen not single.'],
        self::RESIDENT_MEDICAL_HISTORY_CONDITION_NOT_FOUND_EXCEPTION => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Resident Medical History Condition not found.'],
        self::MEDICAL_HISTORY_CONDITION_NOT_SINGLE_EXCEPTION         => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Medical History Condition not single.'],
        self::RESPONSIBLE_PERSON_NOT_FOUND_EXCEPTION                 => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Responsible person not found.'],
        self::MISSING_BASE_RATE_FOR_CARE_LEVEL                       => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Missing Base Rate for some Care Levels.'],
        self::START_GREATER_VALID_THROUGH_DATE_EXCEPTION             => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Start date is always prior to the Valid Through date.'],
        self::INVALID_DISCHARGE_DATE_EXCEPTION                       => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => ''],
        self::NAME_NOT_BE_BLANK_EXCEPTION                            => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Name cannot be blank.'],
        self::RESPONSIBLE_PERSON_ROLE_NOT_FOUND_EXCEPTION            => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Responsible person\'s role not found.'],
        self::RESIDENT_DIAGNOSIS_NOT_FOUND_EXCEPTION                 => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Resident Diagnosis not found.'],
        self::DIAGNOSIS_NOT_SINGLE_EXCEPTION                         => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Diagnosis is not single.'],
        self::RESIDENT_RESPONSIBLE_PERSON_NOT_FOUND_EXCEPTION        => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Resident Responsible Person not found.'],
        self::RESIDENT_PHYSICIAN_NOT_FOUND_EXCEPTION                 => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Resident Physician not found.'],
        self::RESIDENT_HAVE_PRIMARY_PHYSICIAN_EXCEPTION              => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Resident already has a primary physician.'],
        self::RESIDENT_PHYSICIAN_SPECIALITY_EXCEPTION_NOT_FOUND      => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Resident physician speciality not found.'],
        self::PHYSICIAN_SPECIALITY_DUPLICATE_REQUEST_EXCEPTION       => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Speciality duplicate request.'],
        self::PAYMENT_SOURCE_NOT_FOUND_EXCEPTION                     => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Payment Source not found.'],
        self::ASSESSMENT_CATEGORY_NOT_FOUND_EXCEPTION                => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Assessment category not found.'],
        self::ASSESSMENT_CARE_LEVEL_GROUP_NOT_FOUND_EXCEPTION        => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Assessment care level group not found.'],
        self::RESIDENT_RENT_NOT_FOUND_EXCEPTION                      => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Resident Rent not found.'],
        self::RESIDENT_RENT_NEGATIVE_REMAINING_TOTAL                 => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Cannot post Resident Rent with negative Remaining Total.'],
        self::ASSESSMENT_CARE_LEVEL_NOT_FOUND_EXCEPTION              => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Assessment Care level not found.'],
        self::ASSESSMENT_FORM_NOT_FOUND_EXCEPTION                    => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Assessment Form not found.'],
        self::ASSESSMENT_NOT_FOUND_EXCEPTION                         => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Assessment not found.'],
        self::GRID_OPTIONS_NOT_FOUND_EXCEPTION                       => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Grid options not found.'],
        self::EVENT_DEFINITION_NOT_FOUND_EXCEPTION                   => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Event Definition not found.'],
        self::ASSESSMENT_CATEGORY_MULTIPLE_EXCEPTION                 => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Assessment category does not have multiple statuses.'],
        self::ASSESSMENT_ROW_NOT_AVAILABLE_EXCEPTION                 => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Assessment Row not available.'],
        self::PHYSICIAN_NOT_BE_BLANK_EXCEPTION                       => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Please select a Physician.'],
        self::RESPONSIBLE_PERSON_NOT_BE_BLANK_EXCEPTION              => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Please select a Responsible Person.'],
        self::ADDITIONAL_DATE_NOT_BE_BLANK_EXCEPTION                 => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Please select an Additional Date.'],
        self::RESIDENT_EVENT_NOT_FOUND_EXCEPTION                     => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Resident Event not found.'],
        self::REPORT_NOT_FOUND_EXCEPTION                             => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Report not found.'],
        self::REPORT_FORMAT_NOT_FOUND_EXCEPTION                      => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Report format not found.'],
        self::REPORT_MISCONFIGURATION_EXCEPTION                      => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Report misconfiguration.'],
        self::FACILITY_BED_NOT_FOUND_EXCEPTION                       => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Facility Bed not found.'],
        self::APARTMENT_BED_NOT_FOUND_EXCEPTION                      => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Apartment Bed not found.'],
        self::ASSESSMENT_ROW_NOT_FOUND_EXCEPTION                     => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Assessment Row not found.'],
        self::START_GREATER_END_DATE_EXCEPTION                       => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Start date is always prior to the End date.'],
        self::END_DATE_NOT_BE_BLANK_EXCEPTION                        => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'End date cannot be blank.'],
        self::INCORRECT_STRATEGY_TYPE_EXCEPTION                      => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Incorrect strategy. Available types: Facility, Apartment or Region.'],
        self::CAN_NOT_REMOVE_BED_EXCEPTION                           => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Bed has a Resident. Move Resident to another bed.'],
        self::LEAD_CONTACT_ORGANIZATION_CHANGED_EXCEPTION            => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'The Organization of Contact has been changed. Please choose another Contact.'],
        self::REGION_CAN_NOT_HAVE_BED_EXCEPTION                      => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Region cannot have a bed.'],
        self::UNHANDLED_RENT_PERIOD_EXCEPTION                        => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Invalid entry for rent period.'],
        self::RESIDENT_ADMISSION_NOT_FOUND_EXCEPTION                 => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Resident Admission not found.'],
        self::USER_ALREADY_INVITED_EXCEPTION                         => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'User is already invited to SeniorCare.'],
        self::USER_NOT_YET_INVITED_EXCEPTION                         => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Invitation key is invalid or expired.'],
        self::SPACE_ALREADY_HAS_OWNER_EXCEPTION                      => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Space already has an owner.'],
        self::LEAD_CARE_TYPE_NOT_FOUND_EXCEPTION                     => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Care Type not found.'],
        self::LEAD_OUTREACH_TYPE_NOT_FOUND_EXCEPTION                 => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Outreach Type not found.'],
        self::LEAD_ACTIVITY_STATUS_NOT_FOUND_EXCEPTION               => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Activity Status not found.'],
        self::LEAD_ACTIVITY_TYPE_NOT_FOUND_EXCEPTION                 => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Activity Type not found.'],
        self::LEAD_REFERRER_TYPE_NOT_FOUND_EXCEPTION                 => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Referrer Type not found.'],
        self::LEAD_ORGANIZATION_NOT_FOUND_EXCEPTION                  => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Organization not found.'],
        self::LEAD_REFERRAL_NOT_FOUND_EXCEPTION                      => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Referral not found.'],
        self::LEAD_ACTIVITY_NOT_FOUND_EXCEPTION                      => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Activity not found.'],
        self::INCORRECT_LEAD_OWNER_TYPE_EXCEPTION                    => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Incorrect owner type. Available types: Lead, Referral or Organization.'],
        self::LEAD_LEAD_NOT_FOUND_EXCEPTION                          => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Lead not found.'],
        self::LEAD_RP_PHONE_OR_EMAIL_NOT_BE_BLANK_EXCEPTION          => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'You must enter a Phone Number, OR an Email.'],
        self::RESIDENT_ADMISSION_ONLY_ADMIT_EXCEPTION                => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'You must first Admit the Resident.'],
        self::LAST_RESIDENT_ADMISSION_NOT_FOUND_EXCEPTION            => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Last Resident Admission not found.'],
        self::RESIDENT_ADMISSION_TWO_TIME_A_ROW_EXCEPTION            => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Cannot add same type of Resident Admission two times in a row.'],
        self::RESIDENT_ADMISSION_ONLY_READMIT_EXCEPTION              => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'You can only Re-admit the Resident.'],
        self::NOTIFICATION_TYPE_NOT_FOUND_EXCEPTION                  => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Notification Type not found.'],
        self::NOTIFICATION_NOT_FOUND_EXCEPTION                       => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Notification not found.'],
        self::CHANGE_LOG_NOT_FOUND_EXCEPTION                         => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Change Log not found.'],
        self::INCORRECT_CHANGE_LOG_TYPE_EXCEPTION                    => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Incorrect Change Log type.'],
        self::DINING_ROOM_NOT_VALID_EXCEPTION                        => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Dining Room not valid.'],
        self::LEAD_ALREADY_JOINED_IN_REFERRAL_EXCEPTION              => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Lead already has a joined referral.'],
        self::INSURANCE_COMPANY_NOT_FOUND_EXCEPTION                  => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Insurance Company not found.'],
        self::RESIDENT_HEALTH_INSURANCE_NOT_FOUND_EXCEPTION          => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Resident Health Insurance not found.'],
        self::TIME_SPAN_IS_GREATHER_THAN_12_MONTHS_EXCEPTION         => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Time span is greater than 12 months.'],
        self::DOCUMENT_NOT_FOUND_EXCEPTION                           => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Document not found.'],
        self::RESIDENT_DOCUMENT_NOT_FOUND_EXCEPTION                  => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Resident Document not found.'],
        self::RESIDENT_ADMIT_ONLY_ONE_TIME_EXCEPTION                 => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'You are allowed to admit a Resident only one time.'],
        self::INVALID_EFFECTIVE_DATE_EXCEPTION                       => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Invalid Effective date.'],
        self::LEAD_CONTACT_NOT_FOUND_EXCEPTION                       => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Contact not found.'],
        self::INCORRECT_RESIDENT_STATE_EXCEPTION                     => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Incorrect Resident state. Available states: active, inactive or no-admission.'],
        self::LEAD_STAGE_CHANGE_REASON_NOT_FOUND_EXCEPTION           => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Stage Change Reason not found.'],
        self::LEAD_FUNNEL_STAGE_NOT_FOUND_EXCEPTION                  => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Funnel Stage not found.'],
        self::LEAD_TEMPERATURE_NOT_FOUND_EXCEPTION                   => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Temperature not found.'],
        self::LEAD_LEAD_FUNNEL_STAGE_NOT_FOUND_EXCEPTION             => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Lead Funnel Stage not found.'],
        self::LEAD_LEAD_TEMPERATURE_NOT_FOUND_EXCEPTION              => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Lead Temperature not found.'],
        self::INCOMPLETE_CHUNK_DATA_EXCEPTION                        => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Incomplete chunk data.'],
        self::DUPLICATE_IMAGE_BY_REQUEST_ID_EXCEPTION                => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Image with this request id already exists.'],
        self::LEAD_OUTREACH_NOT_FOUND_EXCEPTION                      => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Outreach not found.'],
        self::LEAD_CONTACT_ORGANIZATION_NOT_ALLOWED_CHANGE_EXCEPTION => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'You are not allowed to change Contact Organization as this connection is used in Lead Referrals.'],
        self::LEAD_INCORRECT_ACTIVITY_TYPE_EXCEPTION                 => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Incorrect Activity Type.'],
        self::DOCUMENT_CATEGORY_NOT_FOUND_EXCEPTION                  => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Document Category not found.'],
        self::FACILITY_DASHBOARD_NOT_FOUND_EXCEPTION                 => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Facility Dashboard not found.'],
        self::FACILITY_DOCUMENT_NOT_FOUND_EXCEPTION                  => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Facility Document not found.'],
        self::RESIDENT_RENT_INCREASE_NOT_FOUND_EXCEPTION             => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Resident Rent Increase not found.'],
        self::THE_NAME_IS_ALREADY_IN_USE_IN_THIS_SPACE_EXCEPTION     => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'The name is already in use in this space.'],
        self::FACILITY_EVENT_NOT_FOUND_EXCEPTION                     => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Facility Event not found.'],
        self::USER_NOT_BE_BLANK_EXCEPTION                            => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Please select an User.'],
        self::RESIDENT_NOT_BE_BLANK_EXCEPTION                        => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Please select a Resident.'],
        self::NOT_A_VALID_CHOICE_EXCEPTION                           => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'The value you selected is not a valid choice.'],
        self::CORPORATE_EVENT_USER_NOT_FOUND_EXCEPTION               => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Corporate Event User not found.'],
        self::CORPORATE_EVENT_NOT_FOUND_EXCEPTION                    => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Corporate Event not found.'],
        self::DUPLICATE_RESIDENT_EXCEPTION                           => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Please choose different Resident.'],
        self::ASSESSMENT_TYPE_NOT_FOUND_EXCEPTION                    => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Assessment Type not found.'],
        self::ACTIVE_RESIDENT_EXIST_IN_BED_EXCEPTION                 => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Before disable the Bed first assign the Resident to another Bed.'],
        self::FACILITY_ROOM_TYPE_NOT_FOUND_EXCEPTION                 => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Facility Room Type not found.'],
        self::BASE_RATE_NOT_FOUND_EXCEPTION                          => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Base Rate not found.'],
        self::BASE_RATE_NOT_BE_BLANK_EXCEPTION                       => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Base Rate cannot be blank.'],
        self::INVALID_PRIVATE_ROOM_EXCEPTION                         => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Private Room cannot have more than one enabled bed.'],
        self::INVALID_SHARED_ROOM_EXCEPTION                          => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Shared Room cannot have less than two enabled beds.'],
        self::FACILITY_ROOM_TYPE_FACILITY_CHANGED_EXCEPTION          => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'The Facility of Room Type has been changed. Please choose another Room Type.'],
        self::RENT_REASON_NOT_FOUND_EXCEPTION                        => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Rent Reason not found.'],
        self::DUPLICATE_BASE_RATE_BY_DATE_EXCEPTION                  => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Base Rate with this date and Room Type already exists.'],
        self::PAYMENT_SOURCE_DUPLICATE_BASE_RATE_BY_DATE_EXCEPTION   => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Base Rate with this date and Payment Source already exists.'],
        self::SUBJECT_NOT_BE_BLANK_EXCEPTION                         => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Subject cannot be blank.'],
        self::CSV_REPORT_NOT_FOUND_EXCEPTION                         => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'CSV Report not found.'],
        self::CSV_REPORT_HASH_HAS_EXPIRED                            => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'CSV Report Hash has expired.'],
        self::FILE_EXTENSION_FOUND_EXCEPTION                         => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'File extension not found.'],
        self::ACTIVE_RESIDENT_EXIST_IN_ROOM_EXCEPTION                => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Room is currently occupied. Please move its Resident(s) before deleting this room.'],
        self::LEAD_CURRENT_RESIDENCE_NOT_FOUND_EXCEPTION             => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Current Residence not found.'],
        self::LEAD_HOBBY_NOT_FOUND_EXCEPTION                         => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Hobby not found.'],
        self::LEAD_QUALIFICATION_REQUIREMENT_NOT_FOUND_EXCEPTION     => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Qualification Requirement not found.'],
        self::RESIDENT_READMIT_ONLY_AFTER_DISCHARGE_EXCEPTION        => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'You are allowed only to re-admit a Resident after a discharge state.'],
        self::INVALID_BILL_THROUGH_DATE_EXCEPTION                    => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Effective Date is always prior or equal to the Bill Through Date.'],
        self::RP_PAYMENT_TYPE_NOT_FOUND_EXCEPTION                    => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'RP Payment Type not found.'],
        self::EXPENSE_ITEM_NOT_FOUND_EXCEPTION                       => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Expense Item not found.'],
        self::HOSPICE_PROVIDER_NOT_FOUND_EXCEPTION                   => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Hospice Provider not found.'],
        self::HOSPICE_PROVIDER_NOT_BE_BLANK_EXCEPTION                => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Please select a Hospice Provider.'],
        self::CREADIT_ITEM_NOT_FOUND_EXCEPTION                       => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Credit Item not found.'],
        self::CAN_BE_CHANGED_IS_REQUIRED_EXCEPTION                   => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Can be Changed is required when default amount provided.'],
        self::LEAD_EMAIL_REVIEW_TYPE_NOT_FOUND_EXCEPTION             => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Email Review Type not found.'],
        self::LEAD_WEB_EMAIL_NOT_FOUND_EXCEPTION                     => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Web Email not found.'],
        self::RESIDENT_LEDGER_NOT_FOUND_EXCEPTION                    => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Resident Ledger not found.'],
        self::RESIDENT_EXPENSE_ITEM_NOT_FOUND_EXCEPTION              => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Resident Expense Item not found.'],
        self::RESIDENT_CREADIT_ITEM_NOT_FOUND_EXCEPTION              => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Resident Credit Item not found.'],
        self::RESIDENT_PAYMENT_RECEIVED_ITEM_NOT_FOUND_EXCEPTION     => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Resident Payment Received Item not found.'],
        self::RESIDENT_AWAY_DAYS_NOT_FOUND_EXCEPTION                 => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Resident Away Days not found.'],
        self::KEY_FINANCE_DATES_NOT_FOUND_EXCEPTION                  => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Key Finance Dates not found.'],
        self::DISCOUNT_ITEM_NOT_FOUND_EXCEPTION                      => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Discount Item not found.'],
        self::RESIDENT_LEDGER_ALREADY_EXIST_EXCEPTION                => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Resident Ledger already exist for this year and month.'],
        self::RESIDENT_DISCOUNT_ITEM_NOT_FOUND_EXCEPTION             => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Resident Discount Item not found.'],
        self::LATE_PAYMENT_NOT_FOUND_EXCEPTION                       => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Late Payment not found.'],
        self::START_AND_END_DATE_NOT_SAME_MONTH_EXCEPTION            => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Start and End Dates should be the same month and year.'],
        self::DATES_OVERLAP_EXCEPTION                                => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Dates overlap with already existing Away Days.'],
        self::INCORRECT_REPORT_PARAMETER                             => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Incorrect report parameter(s): %s.'],
        self::INVALID_GRANT_CONFIG                                   => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Invalid Grant configuration.'],
        self::DEFAULT_ROLE_NOT_FOUND_EXCEPTION                       => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Default Role not found.'],
        self::RESOURCE_NOT_FOUND_EXCEPTION                           => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Requested resource not found.'],
        self::FEEDBACK_EXCEPTION                                     => ['httpCode' => Response::HTTP_BAD_REQUEST, 'message' => 'Unknown error occurred during sending your message. Please try again later...'],
    ];
}
