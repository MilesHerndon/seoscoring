<?php
namespace Craft;

class SeoScoringPlugin extends BasePlugin
{
    public function init()
    {
        craft()->on('entries.onSaveEntry', function(Event $event)
        {
            $entry = $event->params['entry'];

            craft()->seoScoring->compileSeoTables($entry);
        });
        // include stylesheet
        if ( craft()->request->isCpRequest() && craft()->userSession->isLoggedIn() )
        {
            craft()->templates->includeCssResource('seoscoring/stylesheets/style.css');
        }
    }
    public function getName()
    {
         return Craft::t('SEO Scoring');
    }

    public function getVersion()
    {
        return '1.0';
    }

    public function getDeveloper()
    {
        return 'MilesHerndon';
    }

    public function getDeveloperUrl()
    {
        return 'http://milesherndon.com';
    }

    // HOOKS
    public function defineAdditionalEntryTableAttributes()
    {
        return array(
            'target_keyword'=>'Primary Target Keyword'
        );
    }

    public function getEntryTableAttributeHtml($entry, $attribute)
    {
        if ($attribute == 'target_keyword')
        {
            $seoInfo = craft()->seoScoring->getSeoInfo($entry->id);
            $tab_num = craft()->seoScoring->getTheTab($entry);
            $keyword = isset($seoInfo[0]) ? '<a href="'. UrlHelper::getUrlWithParams($entry->cpEditUrl, array('tab'=> (string)$tab_num)).'" class="'.strtolower($seoInfo[0]['final_rating']).'">'.$seoInfo[0]['keyword'].'</a>' : '';
            if(isset($seoInfo[0]) && count($seoInfo)>1)
            {
                $keyword .= '<a href="'. UrlHelper::getUrlWithParams($entry->cpEditUrl, array('tab'=> (string)$tab_num)).'">...</a>';
            }
            return $keyword;
        }
    }

}