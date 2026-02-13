<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;

class Setting extends Model implements TranslatableContract
{
    use Translatable;

    protected $table = 'settings';
    public $timestamps = false;
    protected $guarded = ['id'];

    public $translatedAttributes = ['value'];

    public function getValueAttribute()
    {
        return getTranslateAttributeValue($this, 'value');
    }

    static $seoMetas, $socials,$sidebanner,
        $footer, $general, $homeSections, $features,
        $financial, $offlineBanks, $referral, $currencySettings,
        $homeHero, $homeHero2, $homeVideoOrImage,
        $pageBackground, $customCssJs,
        $reportReasons, $notificationTemplates,
        $contactPage, $Error404Page, $navbarLink, $panelSidebar, $findInstructors, $rewardProgram, $rewardsSettings, $storeSettings,
        $registrationPackagesGeneral, $registrationPackagesInstructors, $registrationPackagesOrganizations, $becomeInstructorSection,
        $themeColors, $themeFonts, $forumHomeSection, $cookieSettings, $mobileAppSettings, $remindersSettings, $generalSecuritySettings, $advertisingModal,
        $othersPersonalization, $installmentsSettings, $installmentsTermsSettings, $registrationBonusSettings, $registrationBonusTermsSettings, $statisticsSettings,
        $maintenanceSettings, $generalOptions, $giftsGeneralSettings,$video;

    static $seoMetasName = 'seo_metas';
    static $socialsName = 'socials';
    static $sidebannerName = 'sidebanner';
    static $footerName = 'footer';
    static $generalName = 'general';
    static $featuresName = 'features';
    static $homeSectionsName = 'home_sections';
    static $financialName = 'financial';
    static $offlineBanksName = 'offline_banks';
    static $referralName = 'referral';
    static $currencySettingsName = 'currency_settings';
    static $homeHeroName = 'home_hero';
    static $homeHeroName2 = 'home_hero2';
    static $homeVideoOrImageName = 'home_video_or_image_box';
    static $pageBackgroundName = 'page_background';
    static $customCssJsName = 'custom_css_js';
    static $reportReasonsName = 'report_reasons';
    static $notificationTemplatesName = 'notifications';
    static $contactPageName = 'contact_us';
    static $Error404PageName = '404';
    static $navbarLinkName = 'navbar_links';
    static $panelSidebarName = 'panel_sidebar';
    static $findInstructorsName = 'find_instructors';
    static $rewardProgramName = 'reward_program';
    static $rewardsSettingsName = 'rewards_settings';
    static $storeSettingsName = 'store_settings';
    static $registrationPackagesGeneralName = 'registration_packages_general';
    static $registrationPackagesInstructorsName = 'registration_packages_instructors';
    static $registrationPackagesOrganizationsName = 'registration_packages_organizations';
    static $becomeInstructorSectionName = 'become_instructor_section';
    static $themeColorsName = 'theme_colors';
    static $themeFontsName = 'theme_fonts';
    static $forumHomeSectionName = 'forums_section';
    static $cookieSettingsName = 'cookie_settings';
    static $mobileAppSettingsName = 'mobile_app';
    static $remindersSettingsName = 'reminders';
    static $generalSecuritySettingsName = 'security';
    static $advertisingModalName = 'advertising_modal';
    static $othersPersonalizationName = 'others_personalization';
    static $installmentsSettingsName = 'installments_settings';
    static $installmentsTermsSettingsName = 'installments_terms_settings';
    static $registrationBonusSettingsName = 'registration_bonus_settings';
    static $registrationBonusTermsSettingsName = 'registration_bonus_terms_settings';
    static $statisticsSettingsName = 'statistics';
    static $maintenanceSettingsName = 'maintenance_settings';
    static $generalOptionsName = 'general_options';
    static $giftsGeneralSettingsName = 'gifts_general_settings';
    static $testimonialVideo = 'video';

    static $pagesSeoMetas = ['home', 'search', 'categories', 'classes', 'login', 'register', 'contact', 'blog', 'certificate_validation',
        'instructors', 'organizations', 'instructor_finder_wizard', 'instructor_finder', 'reward_courses', 'products_lists', 'reward_products',
        'forum', 'upcoming_courses_lists','remedies'
    ];
    static $mainSettingSections = ['general', 'financial', 'payment', 'home_hero', 'home_hero2', 'page_background', 'home_video_or_image_box'];
    static $mainSettingPages = ['general', 'financial', 'personalization', 'notifications', 'seo', 'customization', 'other'];

    static $defaultSettingsLocale = 'en';

    static $rootColors = ['primary', "primary-border", "primary-hover", "primary-border-hover",
        "primary-btn-shadow", "primary-btn-shadow-hover", "primary-btn-color", "primary-btn-color-hover",
        'secondary', "secondary-border", "secondary-hover", "secondary-border-hover", "secondary-btn-shadow", "secondary-btn-shadow-hover",
        "secondary-btn-color", "secondary-btn-color-hover"];

    static $rootAdminColors = ['primary'];

    static function getSettingsWithDefaultLocal(): array
    {
        return [
            self::$seoMetasName,
            self::$socialsName,
            self::$generalName,
            self::$financialName,
            self::$offlineBanksName,
            self::$referralName,
            self::$pageBackgroundName,
            self::$homeSectionsName,
            self::$notificationTemplatesName,
            self::$customCssJsName,
            self::$Error404PageName,
            self::$contactPageName,
        ];
    }

    static function getSetting(&$static, $name, $key = null)
    {
        if (!isset($static)) {
            $static = cache()->remember('settings.' . $name, 24 * 60 * 60, function () use ($name) {
                return self::where('name', $name)->first();
            });
        }

        $value = [];

        if (!empty($static) and !empty($static->value) and isset($static->value)) {
            $value = json_decode($static->value, true);
        }

        if (!empty($value) and !empty($key)) {
            if (!empty($value[$key])) {
                return $value[$key];
            } else {
                return null;
            }
        }

        if (!empty($key) and (empty($value) or count($value) < 1)) {
            return '';
        }

        return $value;
    }

    static function getSeoMetas($page = null)
    {
        return self::getSetting(self::$seoMetas, self::$seoMetasName, $page);
    }

    static function getSocials()
    {
        return self::getSetting(self::$socials, self::$socialsName);
    }

    static function getsidebanner()
    {
        return self::getSetting(self::$sidebanner, self::$sidebannerName);
    }

    static function getFooterColumns()
    {
        return self::getSetting(self::$footer, self::$footerName);
    }

    static function getGeneralSettings($key = null)
    {
        return self::getSetting(self::$general, self::$generalName, $key);
    }

    static function getFeaturesSettings($key = null)
    {
        return self::getSetting(self::$features, self::$featuresName, $key);
    }

    static function getCookieSettings($key = null)
    {
        return self::getSetting(self::$cookieSettings, self::$cookieSettingsName, $key);
    }

    static function getFinancialSettings($key = null)
    {
        return self::getSetting(self::$financial, self::$financialName, $key);
    }

    static function getFinancialCurrencySettings($key = null)
    {
        return self::getSetting(self::$currencySettings, self::$currencySettingsName, $key);
    }

    static function getHomeHeroSettings($section = '1')
    {
        if ($section == "2") {
            return self::getSetting(self::$homeHero2, self::$homeHeroName2);
        }

        return self::getSetting(self::$homeHero, self::$homeHeroName);
    }

    static function getHomeVideoOrImageBoxSettings()
    {
        return self::getSetting(self::$homeVideoOrImage, self::$homeVideoOrImageName);
    }

    static function getPageBackgroundSettings($page = null)
    {
        return self::getSetting(self::$pageBackground, self::$pageBackgroundName, $page);
    }

    static function getCustomCssAndJs($key = null)
    {
        return self::getSetting(self::$customCssJs, self::$customCssJsName, $key);
    }

    static function getReportReasons()
    {
        return self::getSetting(self::$reportReasons, self::$reportReasonsName);
    }

    static function getNotificationTemplates($template = null)
    {
        return self::getSetting(self::$notificationTemplates, self::$notificationTemplatesName, $template);
    }

    static function getOfflineBankSettings($key = null)
    {
        return self::getSetting(self::$offlineBanks, self::$offlineBanksName, $key);
    }

    static function getReferralSettings()
    {
        return self::getSetting(self::$referral, self::$referralName);
    }

    static function getContactPageSettings($key = null)
    {
        return self::getSetting(self::$contactPage, self::$contactPageName, $key);
    }

    static function get404ErrorPageSettings($key = null)
    {
        return self::getSetting(self::$Error404Page, self::$Error404PageName, $key);
    }

    static function getHomeSectionsSettings($key = null)
    {
        return self::getSetting(self::$homeSections, self::$homeSectionsName, $key);
    }

    static function getNavbarLinksSettings($key = null)
    {
        return self::getSetting(self::$navbarLink, self::$navbarLinkName, $key);
    }

    static function getPanelSidebarSettings()
    {
        return self::getSetting(self::$panelSidebar, self::$panelSidebarName);
    }

    static function getFindInstructorsSettings()
    {
        return self::getSetting(self::$findInstructors, self::$findInstructorsName);
    }

    static function getRewardProgramSettings()
    {
        return self::getSetting(self::$rewardProgram, self::$rewardProgramName);
    }

    static function getRewardsSettings()
    {
        return self::getSetting(self::$rewardsSettings, self::$rewardsSettingsName);
    }

    static function getStoreSettings($key = null)
    {
        return self::getSetting(self::$storeSettings, self::$storeSettingsName, $key);
    }

    static function getBecomeInstructorSectionSettings()
    {
        return self::getSetting(self::$becomeInstructorSection, self::$becomeInstructorSectionName);
    }

    static function getForumSectionSettings()
    {
        return self::getSetting(self::$forumHomeSection, self::$forumHomeSectionName);
    }

    static function getRegistrationPackagesGeneralSettings($key = null)
    {
        return self::getSetting(self::$registrationPackagesGeneral, self::$registrationPackagesGeneralName, $key);
    }

    static function getRegistrationPackagesInstructorsSettings($key = null)
    {
        return self::getSetting(self::$registrationPackagesInstructors, self::$registrationPackagesInstructorsName, $key);
    }

    static function getRegistrationPackagesOrganizationsSettings($key = null)
    {
        return self::getSetting(self::$registrationPackagesOrganizations, self::$registrationPackagesOrganizationsName, $key);
    }

    static function getThemeColorsSettings()
    {
        return self::getSetting(self::$themeColors, self::$themeColorsName);
    }

    static function getThemeFontsSettings()
    {
        return self::getSetting(self::$themeFonts, self::$themeFontsName);
    }

    static function getMobileAppSettings($key = null)
    {
        return self::getSetting(self::$mobileAppSettings, self::$mobileAppSettingsName, $key);
    }

    static function getRemindersSettings($key = null)
    {
        return self::getSetting(self::$remindersSettings, self::$remindersSettingsName, $key);
    }

    static function getGeneralSecuritySettings($key = null)
    {
        return self::getSetting(self::$generalSecuritySettings, self::$generalSecuritySettingsName, $key);
    }

    static function getAdvertisingModalSettings($key = null)
    {
        return self::getSetting(self::$advertisingModal, self::$advertisingModalName, $key);
    }

    static function getOthersPersonalizationSettings($key = null)
    {
        return self::getSetting(self::$othersPersonalization, self::$othersPersonalizationName, $key);
    }

    static function getInstallmentsSettings($key = null)
    {
        return self::getSetting(self::$installmentsSettings, self::$installmentsSettingsName, $key);
    }

    static function getInstallmentsTermsSettings($key = null)
    {
        return self::getSetting(self::$installmentsTermsSettings, self::$installmentsTermsSettingsName, $key);
    }

    static function getRegistrationBonusSettings($key = null)
    {
        return self::getSetting(self::$registrationBonusSettings, self::$registrationBonusSettingsName, $key);
    }

    static function getRegistrationBonusTermsSettings($key = null)
    {
        return self::getSetting(self::$registrationBonusTermsSettings, self::$registrationBonusTermsSettingsName, $key);
    }

    static function getStatisticsSettings($key = null)
    {
        return self::getSetting(self::$statisticsSettings, self::$statisticsSettingsName, $key);
    }

    static function getMaintenanceSettings($key = null)
    {
        return self::getSetting(self::$maintenanceSettings, self::$maintenanceSettingsName, $key);
    }

    static function getGeneralOptionsSettings($key = null)
    {
        return self::getSetting(self::$generalOptions, self::$generalOptionsName, $key);
    }

    static function gettestimonialVideo($key = null)
    {
        return self::getSetting(self::$video, self::$testimonialVideo, $key);
    }

    static function getGiftsGeneralSettings($key = null)
    {
        return self::getSetting(self::$giftsGeneralSettings, self::$giftsGeneralSettingsName, $key);
    }
}
