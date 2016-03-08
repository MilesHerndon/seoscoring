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
            'seo_score'=>"SEO Score",
            'target_keyword'=>'Primary Target Keyword'
        );
    }

    public function getEntryTableAttributeHtml($entry, $attribute)
    {
        if ($attribute == 'seo_score' || $attribute == 'target_keyword')
            $seoInfo = craft()->seoScoring->getSeoInfo($entry->id);
        if ($attribute == 'seo_score')
        {
            if (isset($seoInfo[0])) {
                $score = $seoInfo;
                return '<span class="'.strtolower($score[0]['final_rating']).'">' . $score[0]['final_rating'] . '</span>';
            }
            else{
                return '';
            }
        }
        if ($attribute == 'target_keyword')
        {
            $keyword = isset($seoInfo[0]) ? $seoInfo[0]['keyword'] : '';
            if(isset($seoInfo[0]) && count($seoInfo)>1)
            {
                $keyword .= " +";
            }
            return $keyword;
        }
    }

}