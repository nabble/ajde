<?php

class Ajde_Crud_Editor_Jwysiwyg extends Ajde_Crud_Editor
{
    public function getResources(Ajde_View &$view)
    {
        /* @var $view Ajde_Template_Parser_Phtml_Helper */

        // Library files
        $view->requireJsPublic('core/jwysiwyg/jwysiwyg.js');
        $view->requireCssPublic('core/jwysiwyg/jwysiwyg.css');

        // Controller
        $view->requireJs('crud/field/text/jwysiwyg', 'html', MODULE_DIR.'_core/',
            Ajde_Document_Format_Html::RESOURCE_POSITION_LAST);
    }
}
