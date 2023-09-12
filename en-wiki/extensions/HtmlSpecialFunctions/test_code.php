<?php
$wikitext=<<<WIKITEXT
{{Projekt3
|intro=
[[File:Oho_tpb_render1.png|thumb]]
<div style="margin:40px"></div>
The trailer for bicycle based on Carla Cargo design is a useful and durable trailer that can be used for a variety of purposes, including hauling cargo, transporting children, and even camping. It is made from accessible materials, including a steel frame.

The trailer is also equipped with a number of features that make it easy to use, including a hitch that attaches to the rear of the bicycle and a brake system.
<div style="margin:100px"></div>
|Images={{ProjektImages
|projectimage=oho_tpb_render1.png
}}{{ProjektImages
|projectimage=Oho tpb 001.jpg
}}
}}
WIKITEXT;

preg_match('/^{{Proje[ck]t[^\n|]*(.*)}}/ms', $wikitext, $matches);
print_r($matches);
preg_match_all('/ \|(\w+)=({{.*?}}|\w*)/ms', $matches[1], $matches);
$a = array_combine($matches[1], $matches[2]);
print_r($a);

//
//$template_fields = explode("|", $wikitext);
//foreach ($template_fields as $field) {
//    if (preg_match("/[a-z]+=/i", $field)) {
//        list($fn, $fv) = explode("=", $field, 2);
//        $fv = preg_replace("/\{\{.*/", "", preg_replace("/\}\}.*/", "", $fv));
//        $fv = str_replace("\n", "", $fv);
//        $fv = trim($fv);
//        if (empty($doc[$fn])) {
//            $doc[$fn] = $fv;
//        } else {
//            if (!is_array($doc[$fn])) {
//                $doc[$fn] = array($doc[$fn]);
//            }
//            $doc[$fn][] = $fv;
//        }
//    }
//}
//print_r(var_dump($doc));