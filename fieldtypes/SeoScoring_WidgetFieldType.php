<?php
namespace Craft;

/**
 * Seo Scoring field type
 */
class SeoScoring_WidgetFieldType extends BaseFieldType
{

    public function getName()
    {
        return Craft::t('SEO Scoring Widget');
    }

    public function getInputHtml($name, $value)
    {

        craft()->templates->includeJsResource('seoscoring/javascript/main.js');
        craft()->templates->includeCssResource('seoscoring/stylesheets/style.css');

        $thisElement = $this->element;
        return craft()->templates->render('seoscoring/_widget', array(
            'name'  => $name,
            'value' => $value,
            'this' => $thisElement
        ));
    }

}