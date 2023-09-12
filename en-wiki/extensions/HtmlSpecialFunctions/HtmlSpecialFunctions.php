<?php

use MediaWiki\MediaWikiServices;

class HtmlSpecialFunctions
{
    public static function onParserSetup($parser)
    {
        $parser->setFunctionHook('addInput', 'HtmlSpecialFunctions::addInput');
        $parser->setFunctionHook('randNumber', 'HtmlSpecialFunctions::randNumber');
        $parser->setFunctionHook('counterIncr', 'HtmlSpecialFunctions::counterIncr');
        $parser->setFunctionHook('counterReset', 'HtmlSpecialFunctions::counterReset');
        $parser->setFunctionHook('counter', 'HtmlSpecialFunctions::counter');
        #$parser->setFunctionHook('loadUserList', 'HtmlSpecialFunctions::loadUserList');
        $parser->setFunctionHook('loadMaterialsComments', 'HtmlSpecialFunctions::loadMaterialsComments');
        $parser->setFunctionHook('loadCommentBySubject', 'HtmlSpecialFunctions::loadCommentBySubject');
        $parser->setFunctionHook('mwCleanHTML', 'HtmlSpecialFunctions::mwCleanHTML');
        $parser->setFunctionHook('mwCategoryLinks', 'HtmlSpecialFunctions::mwCategoryLinks');
        $parser->setFunctionHook('mwIncludeHTML', 'HtmlSpecialFunctions::mwIncludeHTML');
        $parser->setFunctionHook('mwPrintCategoryTree', 'HtmlSpecialFunctions::mwPrintCategoryTree');
        $parser->setFunctionHook('mwGetBestWordsList', 'HtmlSpecialFunctions::mwGetBestWordsList');
        $parser->setFunctionHook('mwGetCustomDataForm', 'HtmlSpecialFunctions::mwGetCustomDataForm');
        $parser->setFunctionHook('askPaginated', 'HtmlSpecialFunctions::askPaginated');
        return true;
    }

    /**
     *
     *
     * @param Parser $parser
     * @param $text
     * @param $strip_state
     * @return true
     * @throws MWException
     */
    public static function onParserBeforeInternalParse(Parser &$parser, &$text, &$strip_state)
    {
        $parser->setFunctionHook('loadUserList', 'HtmlSpecialFunctions::loadUserList');
        return true;
    }

    /**
     * @param DatabaseUpdater $updater
     * @return void
     */
    public static function onLoadExtensionSchemaUpdates(DatabaseUpdater $updater)
    {
        $updater->addExtensionTable(
            'customdataforms',
            dirname(__FILE__) . '/sql/customdataforms.sql'
        );
    }

    /**
     * Things to do when the page is saved
     *
     * applied probably as Hook of the extension at "PageContentSaveComplete":"HtmlSpecialFunctions::onPageContentSaveComplete"
     *
     * @todo  refactor code to use https://www.mediawiki.org/wiki/Manual:Hooks/PageSaveComplete
     * @deprecated since 1.35 and removed in version 1.37.0 See https://www.mediawiki.org/wiki/Manual:Hooks/PageContentSaveComplete use onPageSaveComplete()
     *
     * @param WikiPage $wikiPage
     * @param User $user
     * @param Content $content
     * @param string $summary
     * @param bool $isMinor
     * @param null $isWatch
     * @param null $section
     * @param int $flags
     * @param Revision|null $revision
     * @param Status $status
     * @param int|false $originalRevId
     * @param int $undidRevId
     *
     * @return boolean
     * @see https://www.mediawiki.org/wiki/Manual:Hooks/PageContentSaveComplete
     */
    public static function onPageContentSaveComplete($wikiPage, $user, $content, $summary, $isMinor, $isWatch, $section, $flags, $revision, $status, $originalRevId, $undidRevId)
    {
        $contentTXT = $content->getNativeData();

        $doc = self::parseProjectFields($contentTXT);
        $nameES = trim(@$doc['projectnameES']);
        $nameDE = trim(@$doc['projectnameDE']);
        $nameEN = trim(@$doc['projectnameEN']);
        $name = trim(@$doc['projectname']);

        if (empty($name)) {
            return true;
        } elseif (empty($nameES)) {
            $nameES = $name;
            $langA = 'es';
        } elseif (empty($nameEN)) {
            $nameEN = $name;
            $langA = 'en';
        } elseif (empty($nameDE)) {
            $nameDE = $name;
            $langA = 'de';
        }

        $images = (!empty($doc['projectimage'])) ? $doc['projectimage'] : array();
        if (!is_array($images)) {
            $images = array($images);
        }
        $contentImgs = "";
        if (!empty($images)) {
            foreach ($images as $im) {
                $contentImgs .= "{{ProjektImages\n|projectimage=$im\n}}";
            }
        }

        $urlC = (!empty($doc['firstin'])) ? $doc['firstin'] : "";
        $descC = "";

        $langs = array('es', 'de', 'en');
        foreach ($langs as $lang) {
            if ($lang != $langA) {
                $contentNew = "";
                $contentNew .= "{{Projekt\n";
                if ($lang == 'es') {
                    $n = $nameES;
                }
                if ($lang == 'de') {
                    $n = $nameDE;
                }
                if ($lang == 'en') {
                    $n = $nameEN;
                }
                $contentNew .= "|projectname=$n\n";
                if ($lang != 'es') {
                    $contentNew .= "|projectnameES=$nameES\n";
                }
                if ($lang != 'de') {
                    $contentNew .= "|projectnameDE=$nameDE\n";
                }
                if ($lang != 'en') {
                    $contentNew .= "|projectnameEN=$nameEN\n";
                }
                $contentNew .= "|subcat=Projects\n";
                $contentNew .= "|Images=$contentImgs\n";
                $contentNew .= "|firstin=$urlC\n";
                $contentNew .= "|desc=$descC\n";
                $contentNew .= "}}\n";

                $contentNew = str_replace("\n", "", $contentNew);
                $n = str_replace("\n", "", $n);
                $params = "t=" . rawurlencode(base64_encode($n)) . "&b=" . rawurlencode(base64_encode($contentNew)) . "&u=" . rawurlencode($user->getId());
                //$params = "t=&b=u=";

                $title = Title::newFromText("$lang:Special:HtmlSpecialFunctions");
                $url = $title->getFullURL();
                if (!empty($url)) {
                    $url = $url . "?" . $params;
                    //$r = file_get_contents($url);
                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 100);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_USERAGENT, 'OHO Interwiki Translations');
                    $r = curl_exec($ch);
                    curl_close($ch);
                    //print_r($r);
                    //echo $url." => ".$r; exit;
                }
            }
        }

        return true;
    }

    /**
     * Simple parsing of a template’s fields (assuming a simple template structure)
     *
     * @param $wikitext
     * @return array containing array-keys as template fields and array-values as content
     */
    private function parseProjectFields($wikitext)
    {

        $doc = array();
        $template_fields = explode("|", $wikitext);
        foreach ($template_fields as $field) {
            if (preg_match("/[a-z]+=/i", $field)) {
                list($fn, $fv) = explode("=", $field, 2);
                $fv = preg_replace("/\{\{.*/", "", preg_replace("/\}\}.*/", "", $fv));
                $fv = str_replace("\n", "", $fv);
                $fv = trim($fv);
                if (empty($doc[$fn])) {
                    $doc[$fn] = $fv;
                } else {
                    if (!is_array($doc[$fn])) {
                        $doc[$fn] = array($doc[$fn]);
                    }
                    $doc[$fn][] = $fv;
                }
            }
        }
        return $doc;
    }

    /**
     * @param User $user
     * @param $inject_html
     * @return void
     */
    public static function checkForUserPage(User &$user, &$inject_html)
    {
        /*
        $title = Title::newFromText('User:'.$user->mName);
        if (!is_null($title) && !$title->exists()) {
          $page = new WikiPage($title);
          $pageContent = new WikitextContent("");
          $page->doEditContent($pageContent, 'create user page', EDIT_NEW);
        }
        */
    }

    /**
     * @param $parser
     * @param $askQuery
     * @param $params
     * @param $pageitems
     * @return array
     */
    public static function askPaginated($parser, $askQuery = '', $params = '', $pageitems = 40)
    {
        $p = (!empty($_GET["page"]) && is_numeric($_GET["page"]) && $_GET["page"] > 0) ? $_GET["page"] : 1;

        global $wgHooks;
        $wgHooks['BeforePageDisplay'][] = function (OutputPage &$out, Skin &$skin) {
            $out->addModules('ext.srf.listwidget');
        };

        #$wgUser = RequestContext::getMain()->getUser();
        #$parserOptions = new ParserOptions($wgUser);
        #global $wgTitle;

        $askCount = "{{#ask:$askQuery|format=count}}";
        #$subpages = $parser->parse($askCount, $wgTitle, $parserOptions)->getText();
        $subpages = $parser->internalParse($askCount, false);
        $subpages = preg_replace('/[^0-9.]+/', '', $subpages);
        $pageCount = ceil($subpages / $pageitems);

        #echo "$subpages, $pageCount, '$askCount'";

        $p = ($p > $pageCount) ? $pageCount : $p;
        $start = $pageitems * ($p - 1);

        $pgntVisb = 20;
        $pgntS = $p - floor($pgntVisb / 2);
        $pgntE = $p + floor($pgntVisb / 2);
        if ($pgntS <= 0) {
            $pgntE += abs($pgntS) + 1;
            $pgntS = 1;
        }
        if ($pgntE > $pageCount) {
            $pgntS -= $pgntE - $pageCount;
            if ($pgntS <= 0) {
                $pgntS = 1;
            }
            $pgntE = $pageCount;
        }

        $urlquery = $_GET;

        $urlquery['page'] = 1;
        $urlp = http_build_query($urlquery, "", "&");
        $rpgnt = "<a class=\"first_link no_more\" href=\"?$urlp\">First</a>";
        for ($i = $pgntS; $i <= $pgntE; $i++) {
            $cls = ($i == $p) ? "active_page" : "";
            if ($i == $pgntS) {
                $cls .= " first";
            }

            $urlquery['page'] = $i;
            $urlp = http_build_query($urlquery, "", "&");
            $rpgnt .= "<a class=\"page_link $cls\" href=\"?$urlp\">$i</a>";
        }

        $urlquery['page'] = $pageCount;
        $urlp = http_build_query($urlquery, "", "&");
        $rpgnt .= "<a class=\"last_link\" href=\"?$urlp\">Last</a>";

        $rpgnt = "<div class=\"srf-listwidget-navigation\">$rpgnt</div>";

        $params = str_replace(";", "|", $params);
        $params = (!empty($params)) ? "|$params" : '';
        $ask = "{{#ask:$askQuery$params|limit=$pageitems|offset=$start}}";
        #echo "$ask";

        $return_html = "<div id=\"mw-pages\" class=\"search_items_results_page\">\n";
        $return_html .= $rpgnt;
        $return_html .= "<div>\n";
        #$return_html .= $parser->parse($ask, $wgTitle, $parserOptions)->getText();
        $return_html .= $parser->internalParse($ask, false);
        $return_html .= "\n</div>";
        $return_html .= $rpgnt;
        $return_html .= "\n</div>";

        return array($return_html, 'noparse' => true, 'isHTML' => true);
    }

    /**
     * Add an input form field
     *
     * @param $parser
     * @param $class
     * @param $val
     * @param $id
     * @param $type
     * @return array
     */
    public static function addInput($parser, $class = '', $val = '', $id = '', $type = 'text')
    {
        foreach (['id', 'class', 'val', 'type'] as $php_var) {
            $$php_var = str_ireplace('"', '', $$php_var);
        }
        /*
        $id = str_replace('"', '', $id);
        $class = str_replace('"', '', $class);
        $val = str_replace('"', '', $val);
        $type = str_replace('"', '', $type);
        */
        $html = sprintf('<input id="%s" class="%s" value="%s" type="%s"/>', $id, $class, $val, $type);

        return array($html, 'noparse' => true, 'isHTML' => true);
    }

    /**
     * @param $parser
     * @param $min
     * @param $max
     * @return array
     */
    public static function randNumber($parser, $min = 0, $max = null)
    {
        $max = ($max === null) ? getrandmax() : $max;
        $n = rand($min, $max);

        return array($n, 'noparse' => true, 'isHTML' => false);
    }

    /**
     * Set or increase a global counter
     * @param $parser
     * @param string $var_counter global name of the counter (default: x)
     * @return void
     */
    public static function counterIncr($parser, string $var_counter = '')
    {
        $var_counter = preg_replace("/[^a-z0-9]+/i", "", $var_counter);
        if ('' == $var_counter) $var_counter = 'x';

        if (empty($GLOBALS[$var_counter])) {
            $GLOBALS[$var_counter] = 0;
        }
        $GLOBALS[$var_counter]++;
    }

    /**
     * @param $parser
     * @param string $var_counter global name of the counter (default: x)
     * @return void
     */
    public static function counterReset($parser, string $var_counter = '')
    {
        $var_counter = preg_replace("/[^a-z0-9]+/i", "", $var_counter);
        if ('' == $var_counter) $var_counter = 'x';

        if (!empty($GLOBALS[$var_counter])) {
            unset($GLOBALS[$var_counter]);
        }
    }

    /**
     * @param $parser
     * @param string $var_counter global name of the counter (default: x)
     * @return array
     */
    public static function counter($parser, string $var_counter = '')
    {
        $var_counter = preg_replace("/[^a-z0-9]+/i", "", $var_counter);
        if ('' == $var_counter) $var_counter = 'x';

        $this_count = empty($GLOBALS[$var_counter]) ? 0 : $GLOBALS[$var_counter];

        return array($this_count, 'noparse' => true, 'isHTML' => false);
    }

    /**
     * Get a list of (all) users
     *
     * @param Parser $parser
     * @param $text
     * @param $strip_state
     * @param $usergroup
     * @param string $output_separator list output separator
     * @return void
     * @throws MWException
     */
    public static function loadUserList(Parser &$parser, &$text, &$strip_state, $usergroup = '', $output_separator = ',')
    {
        $Request = new FauxRequest(array('action' => 'query', 'list' => 'allusers'));
        $ApiMain = new ApiMain($Request);
        $ApiMain->execute();
        $data = $ApiMain->getResult()->getResultData();
        //print_r($data);
        $user_list = array();
        $all_users = $data['query']['allusers'];
        if (!empty($all_users)) {
            foreach ($all_users as $user_data) {
                $user_list[] = $user_data['name'];
            }
        }
        $html = implode($output_separator, $user_list);
        echo $html;
        #return array($html, 'noparse'=>false, 'isHTML'=>false);
    }

    /**
     *
     * @todo document code; does it depend on MediaWiki JavaScript code?
     * @param $parser
     * @return array
     */
    public static function loadMaterialsComments($parser)
    {
        $urld = $parser->getTitle()->getTalkPage()->getFullURL();
        $urld .= (stripos($urld, '?') !== false) ? "&" : "?";
        $urld .= 'arnd=' . rand(1000000, 9999999);
        $html = sprintf("<script type=\"text/javascript\">var urldiscussion = '%s';</script>", $urld);
        return array($html, 'noparse' => true, 'isHTML' => true);
    }

    /**
     *
     * @todo document code and meaning
     * @param $parser
     * @param $subject
     * @return array
     * @throws MWException
     */
    public static function loadCommentBySubject($parser, $subject = '')
    {
        $selHTML = '';
        //echo "SUB: $subject<br/>\n";
        $talkp = $parser->getTitle()->getTalkPage()->getFullText();
        if (!empty($GLOBALS["hsf_$talkp"])) {
            $htmlc = $GLOBALS["hsf_$talkp"];
        } elseif (!$parser->getTitle()->isTalkPage()) {
            $page = $parser->getTitle()->getFullText();
            //echo "$page<br/>$talkp";

            //$o = $parser->parse('', $parser->getTitle()->getTalkPage(), $parser->getOptions(), true, false);
            $req = new FauxRequest(array('action' => 'parse', 'page' => $talkp));
            $main = new ApiMain($req);
            $main->execute();
            $data = $main->getResult()->getResultData();
            $htmlc = $data['parse']['text'];
            $GLOBALS["hsf_$talkp"] = $htmlc;
            //$o = $parser->parse('', $parser->getTitle(), $parser->getOptions(), true, false);
        }
        if (!empty($htmlc)) {
            //echo htmlentities($htmlc);
            $domDocument = new DOMDocument('1.0', 'UTF-8');
            $domDocument->preserveWhiteSpace = false;
            $domDocument->substituteEntities = false;
            @$domDocument->loadHTML($htmlc);

            $xpath = new DOMXPath($domDocument);
            $pdiv = $xpath->query('//div[@class="mw-parser-output"]');
            if ($pdiv->length > 0) {
                $subject = trim(mb_strtolower($subject, 'UTF-8'));
                $getComments = $hasRoot = false;
                for ($i = 1; $i <= 3; $i++) {
                    foreach ($pdiv->item(0)->childNodes as $cnode) {
                        //echo $cnode->tagName;
                        if ($cnode->tagName == "h$i") {
                            $hasRoot = true;
                            $csubject = mb_strtolower($cnode->nodeValue, 'UTF-8');
                            $csubject = trim(preg_replace("/ -- .*/", "", $csubject));
                            //echo "$csubject <==> $subject<br/>\n";
                            if ($csubject == $subject) {
                                $getComments = true;
                            } else {
                                $getComments = false;
                            }
                        }
                        if ($getComments) {
                            //echo $cnode->nodeValue.'<br/>';
                            $selHTML .= $domDocument->saveHTML($cnode);
                        }
                    }
                    if ($hasRoot) {
                        break;
                    }
                }
            }
        }
        return array($selHTML, 'noparse' => true, 'isHTML' => true);
    }

    /**
     * @param $parser
     * @param $text
     * @param $max
     * @return array
     */
    public static function mwCleanHTML($parser, $text = '', $max = 0)
    {
        $output = strip_tags($text);
        $output = str_replace("\r\n", ' ', $output);
        $output = str_replace("\r", ' ', $output);
        $output = str_replace("\n", ' ', $output);
        $output = str_replace("\t", ' ', $output);
        $output = str_replace("\0", ' ', $output);
        $output = str_replace("\x0B", ' ', $output);
        $output = preg_replace("/< *br *\/* *>/i", ' ', $output);
        $output = preg_replace('/[ ]+/ui', ' ', $output);

        if (!empty($max) && is_numeric($max)) {
            $output = trim($output);
            if (mb_strlen($output, "UTF-8") > $max) {
                $output = trim(mb_substr($output, 0, $max, "UTF-8")) . "...";
            }
        }

        return array($output, 'noparse' => false, 'isHTML' => false);
    }

    /**
     * Include a file from /html_includes/
     *
     * Undocumented: It checks for tag <deleteblock> to be removed in the output;
     *
     * @depends array $wgHtmlIncludeBlocks
     * @param $parser
     * @param string $file the file name to be loaded
     * @return array|void
     */
    public static function mwIncludeHTML($parser, $file)
    {
        if (!empty($file) && !preg_match('/[^a-z0-9\.]/i', $file)) {
            $folder = rtrim(dirname(__FILE__), "/") . "/html_includes/";
            if (file_exists("$folder/$file")) {
                $html = str_replace("\n", " ", file_get_contents("$folder/$file"));
                $html = preg_replace("/<deleteblock>.*?<\/deleteblock>/s", "", $html);

                global $wgHtmlIncludeBlocks;
                if (!empty($wgHtmlIncludeBlocks)) {
                    global $wgOut;
                    # $urigths = $wgOut->getUser()->getRights();// old

                    $permissionManager = MediaWikiServices::getInstance()->getPermissionManager();
                    $urigths = $permissionManager->getUserPermissions($wgOut->getUser());
                    //print_r($urigths);
                    foreach ($wgHtmlIncludeBlocks as $blk) {
                        if (in_array($blk, $urigths)) {
                            $html = preg_replace("/<\/?$blk>/", "", $html);
                        } else {
                            $html = preg_replace("/<$blk>.*?<\/$blk>/s", "", $html);
                        }
                    }
                }

                return array($html, 'noparse' => false, 'isHTML' => 'true');
            }
        }
    }

    /**
     * @param $parser
     * @param $words
     * @param $minLen
     * @param $sep
     * @param $maxWords
     * @param $wordsAdd
     * @return array|void
     */
    public static function mwGetBestWordsList($parser, $words, $minLen = 0, $sep = ", ", $maxWords = 0, $wordsAdd = null)
    {
        $wList = array();
        $wList[0] = (!empty($words)) ? preg_split("/[^0-9\p{L}]+/iu", $words) : null;
        $wList[1] = (!empty($wordsAdd)) ? explode(",", $wordsAdd) : null;
        $wListF = array();
        foreach ($wList as $i => $wListS) {
            if (!empty($wListS)) {
                foreach ($wListS as $w) {
                    $wf = trim($w);
                    if (!empty($wf) && ($i == 1 || empty($minLen) || mb_strlen($wf, "UTF-8") >= $minLen)) {
                        $wfp = mb_strtoupper(mb_substr($wf, 0, 1, "UTF-8"), "UTF-8");
                        $wfp .= mb_strtolower(mb_substr($wf, 1, null, "UTF-8"), "UTF-8");
                        if (empty($wListF[$wfp])) {
                            $wListF[$wfp] = 0;
                        }
                        $wListF[$wfp]++;
                    }
                }
            }
        }

        if (!empty($wListF)) {
            arsort($wListF, SORT_NUMERIC);
            $wListF = array_keys($wListF);
            if (!empty($maxWords)) {
                $wListF = array_slice($wListF, 0, $maxWords);
            }
            $html = implode($sep, $wListF);
            return array($html, 'noparse' => false, 'isHTML' => 'true');
        }
    }

    /**
     * Custom category tree
     *
     * @param $parser
     * @param string $category
     * @return array
     */
    public static function mwPrintCategoryTree($parser, $category = '')
    {
        $category = "Category:$category";
        $listCats = array();
        $listCats[$category] = 1;

        $CatTitle = Title::newFromText($category);
        $category_requested = $CatTitle->getText();
        $Category = Category::newFromTitle($CatTitle);
        $CatMembers = $Category->getMembers();
        foreach ($CatMembers as $SubCat) {
            if ($SubCat->getNamespaceKey('') == 'category') {
                $subcat_key = 'Category:' . $SubCat->getText();
                $listCats[$subcat_key] = 1;
            }
        }

        $tree = array();
        if (!empty($listCats)) {
            foreach ($listCats as $cat => $cnt) {
                $parent_cat_list = array(); // ??
                $parent_cat_list[] = $cat;
                $Title = Title::newFromText($cat);
                $this_parent_categories = $Title->getParentCategoryTree();
                while (!empty($this_parent_categories)) {
                    reset($this_parent_categories);
                    $pn = key($this_parent_categories);
                    $this_parent_categories = $this_parent_categories[$pn];
                    $parent_cat_list[] = $pn;
                }
                $parent_cat_list = array_reverse($parent_cat_list);
                self::buildTree($tree, $parent_cat_list, $cnt);
            }
        }
        //print_r($tree);
        arsort($listCats);

        $countSel = $countCats = 0;
        $args = null;
        $r = self::printTree($tree, $category_requested, $countSel, $args, null, null, $countCats, 2);
        return array($r, 'noparse' => false, 'isHTML' => true);
    }

    /**
     *
     * @todo document the code and meaning
     *
     * @depends on Semantic MediaWiki #ask function
     *
     * @param $parser
     * @param $askQuery
     * @param $queryForm
     * @param $catarg
     * @param $titlecatshow
     * @param $titleshow
     * @return array
     */
    public static function mwCategoryLinks($parser, $askQuery = '', $queryForm = '', $catarg = '', $titlecatshow = '', $titleshow = '')
    {
        #$parserOptions = new ParserOptions;
        #$data = $parser->parse($query, $parser->getTitle(), $parserOptions)->getText();
        #return array($query, 'noparse'=>true, 'isHTML'=>true);
        $excludec = array('Seiten mit defekten Dateilinks');

        $listCats = array();
        $cntAll = 0;

        #$wgUser = RequestContext::getMain()->getUser();
        #$parserOptions = new ParserOptions($wgUser);
        #global $wgTitle;

        $sep = "@@#@@";
        $valuesep = "@@-@@";
        $ask = "{{#ask:$askQuery|?Category|format=plainlist|link=none|limit=1000|sep=$sep|valuesep=$valuesep|headers=hide}}";
        //$data = $parser->parse($ask, $wgTitle, $parserOptions)->getText();
        $data = $parser->internalParse($ask, false);
        #echo $data;
        $data = explode($sep, $data);

        if (!empty($data)) {
            foreach ($data as $row) {
                $row = preg_replace("/.* \((.*)\)$/i", "$1", $row);
                $fields = explode($valuesep, $row);
                $hasCat = false;
                if (!empty($fields)) {
                    foreach ($fields as $f) {
                        $fc = preg_replace("/^(?:Kategorie|Category|Categoría):/i", "", $f);
                        if (preg_match("/^(?:Kategorie|Category|Categoría):/i", $f) && !in_array($fc, $excludec)) {
                            if (empty($listCats[$f])) {
                                $listCats[$f] = 0;
                            }
                            $listCats[$f]++;
                            $hasCat = true;
                        }
                    }
                }
                if ($hasCat) {
                    $cntAll++;
                }
            }
        }

        $tree = array();
        if (!empty($listCats)) {
            foreach ($listCats as $cat => $cnt) {
                $plist = array();
                $plist[] = $cat;
                $CatTitle = Title::newFromText($cat);
                $p = $CatTitle->getParentCategoryTree();
                while (!empty($p)) {
                    reset($p);
                    $pn = key($p);
                    $p = $p[$pn];
                    $plist[] = $pn;
                }
                $plist = array_reverse($plist);
                self::buildTree($tree, $plist, $cnt);
            }
        }
        //print_r($tree);
        arsort($listCats);

        $args = array_merge($_GET, $_POST);
        //$args = $args[$queryForm];
        //$args[$queryForm] = $args;
        $url = $_SERVER['REQUEST_URI'];
        $url = preg_replace("/^https?:\/\//i", "", $url);
        $url = preg_replace("/^[^\/]+/i", "", $url);
        $url = preg_replace("/\?.*$/i", "", $url);
        //$urlh = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https': 'http';
        $url = "//{{SERVERNAME}}$url";
        //$url = "Special:RunQuery/$queryForm";

        $category_selected = empty($args[$queryForm][$catarg]) ? '' : $args[$queryForm][$catarg];
        unset($args[$queryForm][$catarg]);

        $class = ($category_selected == '') ? 'selected' : '';
        $urlc = "{$url}?" . http_build_query($args, null, '&');
        $return_html =<<<HTML
        <div class="cats_links_filters">
        <ul><li class="$class">
        <a href="$urlc" >All ($cntAll)</a>
        </li></ul>
HTML;
        $countSel = $cntAll;
        $countCats = 0;
        $return_html .= self::printTree($tree, $category_selected, $countSel, $args, $queryForm, $catarg, $countCats, 2);


        if (!empty($listCats) && 1 == 2) {
            foreach ($listCats as $cat => $cnt) {
                $catc = preg_replace("/^.*:/i", "", $cat);
                $argc = $args;
                $argc[$queryForm][$catarg] = $catc;
                $class = ($catc == $category_selected) ? 'selected' : '';
                $countSel = ($catc == $category_selected) ? $cnt : $countSel;

                $urlc = "$url?" . http_build_query($argc, null, '&');
                $return_html .= sprintf('<a href="%s" class="%s">%s (%s)</a>', $urlc, $class, $catc, $cnt);
                $countCats++;
            }
        }
        $return_html .= "</div>\n";

        if (!empty($titlecatshow)) {
            $return_html = sprintf(
                '<div class="count_cats_results_box">%s</div>%s',
                str_replace('[x]', $countCats, $titlecatshow),
                $return_html);
            $return_html = sprintf('<div class="cats_box">%s</div>', $return_html);
        }
        if (!empty($titleshow)) {
            $titleshow = str_replace('[x]', $countSel, $titleshow);
            $CatTitle = Title::newFromText("Category:$category_selected");
            $catselL = (!empty($category_selected)) ? $CatTitle->getFullURL() : "";
            $catlinkr = (!empty($catselL)) ? " - <a href=\"$catselL\">see all results in $category_selected</a>" : "";
            $titleshow = str_replace('[catlink]', $catlinkr, $titleshow);
            $titleshow = '<div class="count_results_box">' . $titleshow . '</div>';
            $return_html = $return_html . $titleshow;
        }

        return array($return_html, 'noparse' => false, 'isHTML' => true);
    }

    /**
     * Rebuild a nested (category) tree itself
     *
     * @param array $tree
     * @param array $categories
     * @param integer $cat_count
     * @return void
     */
    private function buildTree(&$tree, array $categories, $cat_count)
    {
        if (!empty($categories)) {
            $this_cat_value = array_shift($categories);
            $CatTitle = Title::newFromText($this_cat_value);
            $this_cat_value = $CatTitle->getPrefixedText();
            if (empty($tree[$this_cat_value])) {
                $cnt_level = empty($categories) ? $cat_count : 0;
                $tree[$this_cat_value] = array('cnt' => $cnt_level, 'childs' => array());
            } elseif (empty($categories)) {
                $tree[$this_cat_value]['cnt'] += $cat_count;
            }
            self::buildTree($tree[$this_cat_value]['childs'], $categories, $cat_count);
        }
    }

    /**
     * Output a category tree as <ul>
     *
     * @param array $tree a category tree or config
     * @param sting $cat_requested
     * @param $countSel
     * @param $args
     * @param $queryForm
     * @param $catarg
     * @param $countCats
     * @param $startLevel
     * @param $level
     * @return string
     */
    private function printTree($tree, $cat_requested, &$countSel, &$args, $queryForm, $catarg, &$countCats, $startLevel = 1, $level = 0)
    {
        $level++;
        $return_html = '';
        if (!empty($tree)) {
            if ($level < $startLevel) {
                foreach ($tree as $cat_key => $cfg) {
                    $return_html .= self::printTree(
                        $cfg['childs'],
                        $cat_requested,
                        $countSel, $args, $queryForm, $catarg, $countCats, $startLevel, $level
                    );
                }
            } else {
                $return_html .= "<ul>\n";
                foreach ($tree as $cat_key => $cfg) {
                    $ThisTitle = Title::newFromText($cat_key);
                    $cat_display_text = $ThisTitle->getText();
                    //$cat_display_text = preg_replace("/^.*:/i", "", $cat_key);
                    //$class = ($cat_display_text == $cat_requested) ? 'selected' : '';
                    $return_html .= sprintf(
                        '<li class="%s">',
                        ($cat_display_text == $cat_requested) ? 'selected' : ''
                    );

                    $category_url = $ThisTitle->getFullURL();
                    if ($args === null) {
                        $return_html .= sprintf('<a href="%s">%s</a>', $category_url, $cat_display_text);
                    } else {
                        $category_url = "//{{SERVERNAME}}/index.php";
                        $argc = $args;
                        $argc[$queryForm][$catarg] = $cat_display_text;
                        $countSel = ($cat_display_text == $cat_requested) ? $cfg['cnt'] : $countSel;

                        if (!empty($cfg['cnt'])) {
                            $category_url = "$category_url?" . http_build_query($argc, null, '&');
                            $return_html .= "<a href=\"$category_url\">$cat_display_text (" . $cfg['cnt'] . ")</a>\n";
                            $countCats++;
                        } else {
                            $return_html .= "<span class=\"\">$cat_display_text</span>\n";
                        }
                    }
                    $return_html .= self::printTree(
                        $cfg['childs'],
                        $cat_requested, $countSel, $args, $queryForm, $catarg, $countCats, $startLevel, $level
                    );
                    $return_html .= "</li>\n";
                }
                $return_html .= "</ul>\n";
            }
        }
        return $return_html;
    }

    /**
     * @param $parser
     * @param $formid
     * @param string $field_names list of form field names (separated by comma “,”)
     * @param $save_button_txt
     * @return array|void
     */
    public static function mwGetCustomDataForm($parser, $formid, $field_names, $save_button_txt = "Save")
    {
        $html = "";
        if (!empty($field_names)) {
            $field_names = explode(",", $field_names);
            foreach ($field_names as $this_field) {
                $f_name = preg_replace("/[^0-9\p{L}]+/iu", "_", $this_field);
                $f_name = preg_replace("/_+/", "_", $f_name);
                if (!empty($f_name)) {
                    $html .= sprintf('<input type="text" name="%s"/>', $f_name);
                }
            }
        }

        if (!empty($html)) {
            $SpecialPageTitle = Title::newFromText("Special:HtmlSpecialFunctions");
            $url = $SpecialPageTitle->getFullURL();
            $this_javascript=<<<HTML
            <script>
            var pageg = '{$url}';
            $('#custom_data_form_{$formid}').submit(function(e) {e.preventDefault();});
            $('#button_update_{$formid}').click(function() {
                var sdata['formID'] = '{$formid}',
                sdata['sdata'] = $('#custom_data_form_{$formid}').serialize();
              $.post(pageg, sdata.serialize(), function(data) {
                if (data.msg!==undefined) {alert(data.msg);}
                if (data.redir!==undefined) {window.location.href = data.redir;}
              }).fail(function() {
                alert('error');
              });
            });
            </script>
HTML;
            /*
            $js = "<script>";
            $js .= "var pageg = '$url';";
            $js .= "$('#custom_data_form_$formid').submit(function(e) {e.preventDefault();});";
            $js .= "$('#button_update_$formid').click(function() {";
            $js .= "        var sdata['formID'] = '$formid'";
            $js .= "  var sdata['sdata'] = $('#custom_data_form_$formid').serialize()";
            $js .= "  $.post(pageg, sdata.serialize(), function(data) {";
            $js .= "    if (data.msg!==undefined) {alert(data.msg);};";
            $js .= "    if (data.redir!==undefined) {window.location.href = data.redir;};";
            $js .= "  }).fail(function() {";
            $js .= "    alert('error');";
            $js .= "  });";
            $js .= "});";
            $js .= "</script>";
            */

            $html .= sprintf('<button id="button_update_%s">%s</button>', $formid, $save_button_txt);
            $html = sprintf('<form id="custom_data_form_%s">%s</form>%s', $formid, $html, $this_javascript);

            return array($html, 'noparse' => false, 'isHTML' => 'true');
        }
    }
}// HtmlSpecialFunctions
