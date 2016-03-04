<?php
namespace Craft;

class SeoScoringPlugin extends BasePlugin
{
    public function init()
    {
        if ( craft()->request->isCpRequest() && craft()->userSession->isLoggedIn() )
        {
            craft()->templates->includeJsResource('seoscoring/javascript/main.js');
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



}