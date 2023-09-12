<?php

/**
 * @todo What is the view [intention, plan] of this page? TODO document meaning and function of this page.
 */
class SpecialOhoFunctions extends SpecialPage
{
    function __construct()
    {
        parent::__construct('HtmlSpecialFunctions');
    }

    function execute($par)
    {
        global $wgOut;
        $wgOut->disable();
        if ($_SERVER["SERVER_ADDR"] != $_SERVER["REMOTE_ADDR"]) {
            echo 0;
        } else {
            $request = $this->getRequest();

            $sdata = $request->getText('sdata');
            if (!empty($sdata) && !empty($sdata['formID']) && !empty($sdata['data'])) {
                $sdata['data'] = json_encode($sdata['data']);
                global $wgOut;
                $u = @$wgOut->getUser()->getId();
                $u = (!empty($u)) ? $u : 0;

                $lb = MediaWikiServices::getInstance()->getDBLoadBalancer();
                $dbw = $lb->getConnectionRef(DB_PRIMARY);
                $dbw->insert('customdataforms', ['formID' => $sdata['formID'], 'data' => $sdata['data'], 'userID' => $u]);
            } else {
                $t = $request->getText('t');
                $b = $request->getText('b');
                $u = $request->getText('u');
                if (!empty($t) && !empty($b) && !empty($u)) {
                    $t = base64_decode($t);
                    $b = base64_decode($b);
                    $title = Title::newFromText($t);
                    $user = User::newFromId($u);
                    if (!is_null($title) && !$title->exists() && !is_null($user)) {
                        $page = new WikiPage($title);
                        $pageContent = new WikitextContent($b);
                        $page->doEditContent($pageContent, 'Page auto added', EDIT_NEW, false, $user);
                        echo 1;
                    } else {
                        echo 2;
                    }
                } else {
                    echo 3;
                }
            }
        }
    }
}
