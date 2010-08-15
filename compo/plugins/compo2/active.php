<?php

function _compo2_active($params) {
    if (!$params["uid"]) {
        echo "<p class='message'>You must sign in to submit an entry.</p>";
        return _compo2_preview($params);
    }

    $action = isset($_REQUEST["action"])?$_REQUEST["action"]:"default";
    
    if ($action == "default") {
        $ce = compo2_entry_load($params["cid"],$params["uid"]);
        $action = "edit";
        if ($ce["id"]) { $action = "preview"; }
    }
    
    if ($action == "edit") {
        return _compo2_active_form($params);
    } elseif ($action == "save") {
        return _compo2_active_save($params);
    } elseif ($action == "preview") {
        return _compo2_preview($params);
    }
}

function _compo2_active_form($params,$uid="",$is_admin=0) {
    if (!$uid) { $uid = $params["uid"]; }
    $ce = compo2_entry_load($params["cid"],$uid);
    
    if ($params["locked"] && !$ce["id"]) {
        echo "<p class='warning'>This competition is locked.  No new entries are being accepted.</p>";
        return;
    }
    
    $links = unserialize($ce["links"]);
    if (!$ce["id"]) {
        $links = array(
            0=>array("title"=>"Windows","url"=>""),
            1=>array("title"=>"OS/X","url"=>""),
            2=>array("title"=>"Linux","url"=>""),
            3=>array("title"=>"Web","url"=>""),
            4=>array("title"=>"Source","url"=>""),
        );
    }
    
    
    if (!$is_admin) {
        echo "<p><a href='?action=preview'>View all entries.</a></p>";
    }
    
    $star = "<span style='color:#f00;font-weight:bold;'>*</span>";
    
    if (!$is_admin) {
        echo "<h3>Edit your Entry</h3>";
    } else {
        echo "<h3>Edit this Entry</h3>";
    }
    
    if ($ce["id"] != "" && !$ce["active"]) {
        echo "<div class='warning'>Your entry is not complete.</div>";
    }

    $link = "?action=save";
    if ($is_admin) { $link .= "&admin=1&uid=$uid"; }

    echo "<form method='post' action='$link' enctype='multipart/form-data'>";
    echo "<h4>Name of Entry</h4>";
    
    echo "$star <input type='text' name='title' value=\"".htmlentities($ce["title"])."\" size=60> ";
    
    if (isset($params["rules"])) {
    
        echo "<h4>Entry Type</h4>";
        
//         echo "<p style='border: 1px solid #aaa; background:#eee; padding:10px; margin:10px;'><i>We're glad to have you at Ludum Dare!  And we're thrilled you made a game this weekend!  There are two entry types: Competition Entries - those that <a href='{$params["rules"]}' target='_blank'>follow the rules</a> and Game Jam Entries - those that don't!</i></p>";
        
//         echo "<div>&nbsp;</div>";
    
//         $checked = ($ce["rules_ok"]?"checked":"");
//         echo "<p><input type='checkbox' name='rules_ok' value='1' $checked /> This entry abides by the contest <a href='#' target='_blank'>rules</a> and should be judged.</p>";
        echo "<div style='margin-left:20px'>";

        $selected = (strcmp($ce["rules_ok"],"1")==0?"checked":"");
        echo "<input type='radio' name='rules_ok' value='1' $selected /> Competition Entry";
        echo "<div><i>My entry follows all <a href='{$params["rules"]}' target='_blank'>the rules</a> and I want it to be judged.</i></div>";
        echo "<br/>";
        $selected = (strcmp($ce["rules_ok"],"0")==0?"checked":"");
        echo "<input type='radio' name='rules_ok' value='0' $selected /> Game Jam Entry";
        echo "<div><i>My entry doesn't follow the rules or I don't want it to be judged.</i></div>";
        echo "<div>&nbsp;</div>";
        echo "</div>";
        
    }
    
    echo "<h4>Notes</h4>";
    
    echo "<textarea name='notes' rows=8 cols=60>".htmlentities($ce["notes"])."</textarea>";
    
    echo "<h4>Screenshots</h4>";
    
    echo "<p>You must include at least one screenshot.</p>";
    
    $shots = unserialize($ce["shots"]);
//     print_r($shots);
    
    echo "<table>";
    for ($i=0; $i<5; $i++) {
        $k = "shot$i";
        echo "<tr><td>".($i+1).".<td>";
        if ($i==0) { echo "$star "; }
        echo "<td><input type='file' name='$k'>";
        if ($i==0) { echo "<td>(Primary Screenshot)"; }
        if (isset($shots[$k])) {
            echo "<tr><td><td align=left><img src='".compo2_thumb($shots[$k],120,80)."'>";
        }
    }
    echo "</table>";
    
    echo "<h4>Links</h4>";
    
    echo "<p>This is where you link to your entry downloads.  You must include at least one link.</p>";
    
    echo "<table>";
    echo "<tr><th><th>Name<th><th>URL";
    for ($i=0; $i<5; $i++) {
        echo "<tr><td>";
        if ($i==0) { echo " $star"; }
        echo "<td><input type='text' name='links[$i][title]' size=15 value=\"".htmlentities($links[$i]["title"])."\">";
        echo "<td>";
        if ($i==0) { echo " $star"; }
        echo "<td><input type='text' name='links[$i][link]' value=\"".htmlentities($links[$i]["link"])."\" size=45>";
    }
    echo "</table>";
    
/*    echo "<h4>Extra Stuff</h4>";
    // this is non-functional
    echo "<table>";
    echo "<tr><td><input type='checkbox' name='data[pov_toggle]'>";
    echo "<td>Checkbox that PoV wanted for reasons unbeknownst to us.";
    echo "</table>";*/
    
    if ($is_admin) {
        echo "<table>";
        echo "<tr><td>Disabled";
        echo "<td>"; compo2_select("disabled",array("0"=>"No","1"=>"Yes"),$ce["disabled"]);
        echo "</table>";
    }
    
    echo "<p>";
    echo "<input type='submit' value='Save your Entry'>";
    echo "</p>";
    
    echo "</form>";
    
    echo "<p>$star - required field</p>";
}

function _compo2_active_save($params,$uid="",$is_admin=0) {
    if (!$uid) { $uid = $params["uid"]; }
    $ce = compo2_entry_load($params["cid"],$uid);
    
    if ($params["locked"] && !$ce["id"]) {
        echo "<p class='warning'>This competition is locked.  No new entries are being accepted.</p>";
        return;
    }
    
    $active = true; $msg = "";
    
    $ce["title"] = compo2_strip($_REQUEST["title"]);
    if (!strlen($ce["title"])) { $active = false; $msg = "Entry name is a required field."; }
    
    if (isset($params["rules"])) {
        $ce["rules_ok"] = intval($_REQUEST["rules_ok"]);
    }
    
    $ce["notes"] = compo2_strip($_REQUEST["notes"]);
    
    $shots = unserialize($ce["shots"]);
    if ($shots == null) { $shots = array(); }
    for ($i=0; $i<5; $i++) {
        $k = "shot$i"; $fe = $_FILES[$k];
        if (!$fe["tmp_name"]) { continue; }
        list($w,$h) = getimagesize($fe["tmp_name"]);
        if (!$w) { continue; } if (!$h) { continue; }
//         unset($shots[$k]);
        $ext = array_pop(explode(".",$fe["name"]));
        $cid = $params["cid"];
        $ts = time();
//         $fname = "$cid/$uid-$ts.$ext";
        $fname = "$cid/$uid-$k.$ext";
        $dname = dirname(__FILE__)."/../../compo2";
        @mkdir("$dname/$cid");
        $dest = "$dname/$fname";
        move_uploaded_file  ( $fe["tmp_name"] ,$dest );
        $shots[$k] = $fname;
    }
    $ce["shots"] = serialize($shots);
    if (!count($shots)) { $active = false; $msg = "You must include at least one screenshot."; }
    
    foreach ($_REQUEST["links"] as $k=>$le) {
        $_REQUEST["links"][$k] = array(
            "title"=>compo2_strip($le["title"]),
            "link"=>compo2_strip($le["link"]),
        );
    }
    $ce["links"] = serialize($_REQUEST["links"]);
    $ok = false; foreach ($_REQUEST["links"] as $le) {
        if (strlen($le["title"]) && strlen($le["link"])) { $ok = true; }
    }
    if (!$ok) { $active = false; $msg = "You must include at least one link."; }
    
    if ($is_admin) { 
        $ce["disabled"] = $_REQUEST["disabled"];
    }
    if ($ce["disabled"]) { $active = false; $msg = "This entry has been disabled."; }
    
//     $ce["data"] = serialize($_REQUEST["data"]);
    $ce["active"] = intval($active);
    unset($ce["results"]);
    if (!$ce["id"]) {
        $ce["cid"] = $params["cid"];
        $ce["uid"] = $uid;
        $ce["ts"] = date("Y-m-d H:i:s");
        compo2_insert("c2_entry",$ce);
    } else {
        compo2_update("c2_entry",$ce);
    }
    
    echo "<h3>Entry Saved</h3>";
    
    if (!$active) {
        echo "<p class='error'>$msg</p>";
    }
    
    if (!$is_admin) {
        echo "<p><a href='?action=edit'>Edit your entry</a> | <a href='?action=default'>View all entries</a> | <a href='?action=preview&uid={$params["uid"]}'>View your entry</a></p>";
    } else {
        echo "<p><a href='?action=default&admin=1'>View all entries</a></p>";
    }
//     header("Location: ?action=default"); die;
}
?>