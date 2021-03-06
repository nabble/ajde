<?php

class _coreDebuggerController extends Ajde_Controller
{
    public function view()
    {
        if (Ajde::app()->getRequest()->isAjax()) {
            $this->setAction('ajax');
        }

        // Grab the view to easily assign variables
        $view = $this->getView();

        // Get all warnings from Ajde_Dump::warn()
        if (Ajde_Dump::getWarnings()) {
            $view->assign('warn', Ajde_Dump::getWarnings());
        }

        // Get all dumps from Ajde_Dump::dump() [Aliased as a global function dump()]
        if (Ajde_Dump::getAll()) {
            $view->assign('dump', Ajde_Dump::getAll());
        }

        // Get request parameters
        $view->assign('request', Ajde::app()->getRequest());

        // Get Configuration stage
        $view->assign('environment', Ajde_Environment::current());

        // Get database queries
        if (class_exists('Ajde_Db_PDO')) {
            $view->assign('database', Ajde_Db_PDO::getLog());
        }

        // Get language
        $view->assign('lang', Ajde_Lang::getInstance()->getLang());

        // Get session
        $view->assign('session', $_SESSION);

        // Get ACL
        if (class_exists('Ajde_Acl')) {
            $view->assign('acl', Ajde_Acl::getLog());
        }

        // Get the application timer
        Ajde::app()->endTimer(0);
        Ajde::app()->endTimer(Ajde::app()->getLastTimerKey());
        $view->assign('timers', Ajde::app()->getTimers());

        return $this->render();
    }
}
