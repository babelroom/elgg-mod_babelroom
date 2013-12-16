<?php

require_once($CONFIG->pluginspath . "babelroom/vendors/BabelRoomV1API/API.php");

$api_server = elgg_get_plugin_setting('api_server', 'babelroom');

# bottom links
$room_target = '';
if (strlen($vars['entity']->babelroom_target)) {
    # trim helps if a user inadvertly left leading or trailing spaces .. difficult to see, but will break target
    $room_target = ' target="' . trim($vars['entity']->babelroom_target) . '"';
    }
$room_href = elgg_get_site_url() . 'babelroom/join?widget_guid=' . $vars['entity']->getGUID();
$room_link = '<a href="' . $room_href . '"' . $room_target . '>' . elgg_echo('babelroom:widgets:babelroom:view:room_link') . '</a>';

# ---
$cid = BRAPI_elgg_getOrCreateConference($vars['entity']);
$wuid = $vars['entity']->guid;
$ctoken = null;

if ($cid) {
    $result;
    /* only do this once per specific conference for all widgets on page */
    global $_br_tokens;
    if (!isset($_br_tokens))
        $_br_tokens = array();
    if (isset($_br_tokens[$cid]))
        $ctoken = $_br_tokens[$cid];
    else {
        if (BRAPI_elgg_addParticipant($vars['entity'], $cid, $result))
            $ctoken = ($_br_tokens[$cid] = $result->token );
        else
            register_error(elgg_echo('babelroom:errors:server_error'));
        }
    }

# ---
if ($ctoken) {
    global $_br_javascript_include_done;
    if (!$_br_javascript_include_done) {
        /* only emit this once on page */
        echo '<script type="text/javascript" src="'
            .  elgg_get_plugin_setting('cdn_server', 'babelroom')
            . '/cdn/v1/br_api.full.min.js?' . time() . '"></script>';
        #echo '<script type="text/javascript" src="' . elgg_get_site_url() . 'mod/babelroom/br_api.full.min.js"></script>'; // what about virtual hosts? */
        $_br_javascript_include_done = true;
        ?>

        <script type="text/javascript">
        // <![CDATA[
        function _br_singleton_instance(cid) {
            var jscid = '_br_api_' + cid
                , a = window[jscid];
            if (!a) {
                /* api instance not yet created for this conference */
                a = BR.v1.api.create({
                    hosts: "<?php print $api_server?>",
                    authentication: {token:"<?php print $ctoken?>"},
                    conference_id: <?php print $cid?>
                    });
                /* start the conference stream after the DOM is fully loaded */
                jQuery(document).ready(function() { a.start(); });
                window[jscid] = a;
                }
            return a;
            }
        function _br_set_widget_title(wid, subwidgettype) {
            jQuery('#elgg-widget-'+wid+' .elgg-widget-handle > h3').html('BabelRoom: '+subwidgettype);
            }
        // ]]>
        </script>
        <?php
        }
?>






<?php






    /* select babelroom widget type */
    if ($vars['entity']->babelroom_subwidget==='group_chat') {
        #
        # Chat Widget
        #
        echo '<div id="messages_'.$wuid.'" style="height: '.$vars['entity']->babelroom_height.'px; overflow-y: auto; width 100%;"></div>';
        echo elgg_view("input/text", array("id" => "input_$wuid", "style" => "width: 80%;"));
        echo elgg_view('input/submit', array("id"=>"send_$wuid", "value" => elgg_echo('babelroom:widgets:babelroom:view:send')));
?>
<script type="text/javascript">
// <![CDATA[
(function(){
    var messages = null
    var chat_width = null;

    /* --- utils --- */
    function scrollToBottom(messages) {
        messages.prop({scrollTop: messages.prop("scrollHeight")});
        }

    /* --- new incoming message --- */
    function newMessage(msg) {
        var msg_top = '<div style="color: #bbb; border: 0; border-top: 1px solid #ddd;">';
        msg_top += '<div style="float: left; font-weight: bold;">'+msg.user+'</div><div style="float: right;">';
        msg_top += msg.time + '</div><div style="clear: both;"></div></div>';
        var left_width = 50;
        var sb_width = 28;
        if (msg.avatar)
            messages.append('<div style="float: left; width: '+left_width+'px;"><img src="'+msg.avatar+'" alt="'+msg.user+'" title="'+msg.user+'"></div>')
        else
            messages.append('<div style="float: left; width: '+left_width+'px; font-weight: bold; overflow: hidden; padding: 2px;">'+msg.user+'</div>')
        messages 
            .append(
                '<div style="float: right; width: '+(chat_width-(left_width+sb_width))+'px; padding: 0px; padding-right: 5px;">'+msg_top+
                '<div style="padding: 2px;">'+msg.text+'</div></div>')
            .append('<div style="clear: both; height: 20px;"></div>');
        scrollToBottom(messages);
        }

    /* --- clear all messages --- */
    function doClear() {
        messages.html("");
        }

    _br_singleton_instance(<?php print $cid; ?>).addControllers(
                {   type: 'chat',
                    onInit: function(){
                        /* --- setup --- */
                        var _this=this;
                        messages = jQuery("#messages_<?php print $wuid?>");
                        chat_width = messages.width();
                        jQuery("#send_<?php print $wuid?>").click(function(){
                            _this.sendElement("input_<?php print $wuid?>");
                            });
                        jQuery("#input_<?php print $wuid?>").keydown(function(e){
                            if (e.keyCode==13)
                                _this.sendElement("input_<?php print $wuid?>");
                            });
                        },
                    onMessage: newMessage,
                    onClear: doClear
                });
    _br_set_widget_title(<?php print $wuid?>, "<?php print elgg_echo('babelroom:widgets:babelroom:type:group_chat')?>");
})();
// ]]>
</script>






<?php






        }
    else if ($vars['entity']->babelroom_subwidget==='conference') {
        #
        # Summary
        #
        echo '<div><center>';
        echo '<div id="conference_'.$wuid.'" style=""></div>';
        echo '<div id="participants_'.$wuid.'" style=""></div>';
        echo '<form action="' . elgg_get_site_url() . 'babelroom/join" method="GET"' . $room_target . '>';    # could also POST
        echo elgg_view('input/hidden', array('name' => 'widget_guid', 'value' => $vars['entity']->getGUID()));
        echo elgg_view('input/submit', array('value' => elgg_echo('babelroom:widgets:babelroom:view:join')));
        echo '</form>';
        echo '</center></div>';
?>
<script type="text/javascript">
// <![CDATA[
(function(){
    _br_singleton_instance(<?php print $cid?>).addControllers([{
        type: 'summary',
        onLoad: function(cxt) {
            var div = jQuery('#conference_'+<?php print $wuid?>)
                , ht=''
                , fld=function(fld) { return (cxt[fld])?(cxt[fld]):''; }
            ht += '<h4>'+fld('conference_name')+'</h4>';
            ht += fld('conference_description')+'<br>';
            ht += '<b>'+fld('first_name')+' '+fld('last_name')+'</b><br>';
            if (cxt.myAccessInfo) {
                ht += ('<?php print elgg_echo('babelroom:widgets:babelroom:view:access_number')?>'.replace(/%s/g,fld('myAccessInfo')))+'<br>';
                }
            ht += ('<?php print elgg_echo('babelroom:widgets:babelroom:view:pin')?>'.replace(/%s/g,fld('pin')))+'<br>';
            div.html(ht);
            },
        },{
        type: 'participants',
        onCountChange: function(count) {
            var div = jQuery('#participants_'+<?php print $wuid?>);
            div.html(count+' user(s)');
            }
        }]);
    _br_set_widget_title(<?php print $wuid?>, "<?php print elgg_echo('babelroom:widgets:babelroom:type:conference')?>");
})();
// ]]>
</script>






<?php






        }
    else if ($vars['entity']->babelroom_subwidget==='p2p_chat') {
        #
        # Peer-to-Peer
        #
        echo '<div id="p2p_chat'.$wuid.'" style="height: '.$vars['entity']->babelroom_height.'px; overflow-y: auto; width 100%;">';
        echo '  <div id="p2p_chat_incall'.$wuid.'" style="display: none; color: white; background-color: black; width: 100%; height: 100%;">';
        echo '    <div class="br-incoming" style="display: none; width: 100%; height: 100%; text-align: center; padding-top: 20px;">';
        echo '      <div class="br-calling"></div>';
        echo '      <div class="br-ringing"></div>';
        echo '      <div class="br-msg"></div>';
        echo '    </div>';
        echo '    <div class="br-video" style="display: none; width: 100%, height: 100%; position: absolute">';
        echo '      <video id="p2p_chat_remote_video'.$wuid.'" autoplay="autoplay" style="position: absolute;"></video>';
        echo '      <video id="p2p_chat_local_video'.$wuid.'" autoplay="autoplay" style="position: absolute;"></video>';
        echo '      <div class="br-indicators" style="position: absolute;">' . elgg_echo('babelroom:widgets:babelroom:view:indicators') . '</div>';
        echo '      <div class="br-controls" style="position: absolute;">' . elgg_echo('babelroom:widgets:babelroom:view:controls') . '</div>';
        echo '    </div>';
        echo '  </div>';
        echo '  <div id="p2p_chat_participants'.$wuid.'">';
        echo '    <div id="p2p_chat_nousers'.$wuid.'">' . elgg_echo('babelroom:widgets:babelroom:view:no_users') . '</div>';
        echo '  </div>';
        echo '</div>';
?>
<script type="text/javascript">
// <![CDATA[
(function(){
    _br_singleton_instance(<?php print $cid?>).addControllers([{
        type: 'participants',
        excludeSelf: true,
        div: jQuery('#p2p_chat_participants<?php print $wuid?>'),
        nousers: jQuery('#p2p_chat_nousers<?php print $wuid?>'),
        onUpdate: function(id,user) {
            var _this = this;
            var width = this.div.width()
                , name = (user)?((user.name?user.name:'')+' '+(user.last_name?user.last_name:'')):''
                , avatar = (user && user.avatar_small)?user.avatar_small:null
                , left_width = 50
                , sb_width = 28
                , ht = ''
                ;
            ht += '<div class="_br_user'+id+'">';
            if (avatar)
                ht += '  <div style="float: left; width: '+left_width+'px;"><img src="'+avatar+'" alt="'+name+'" title="'+name+'"></div>';
            else
                ht += '  <div style="float: left; width: '+left_width+'px; font-weight: bold; overflow: hidden; padding: 2px;"></div>';
            ht += '  <div style="float: right; width: '+(width-(left_width+sb_width))+'px; padding: 0px; padding-right: 5px;">';
            ht += '    <div style="clor: #bbb; border: 0; border-top: 0px solid #ddd;">';
            ht += '      <div style="width: 80%;">';
            ht += '        <div style="padding: 2px; txt-align: center;">'+name+'</div>';
            ht += '        <div class="_br_user'+id+'_call" style="display: none;"><a href="#"><?php print elgg_echo('babelroom:widgets:babelroom:view:call')?></a><span><?php print elgg_echo('babelroom:widgets:babelroom:view:busy')?></span></div>';
            ht += '      </div>';
            ht += '    </div>';
            ht += '  </div>';
            ht += '</div>';
            ht += '  <div style="clear: both;"></div>';
            if (jQuery('._br_user'+id,this.div).length)    /* does a div for this user already exist? */
                jQuery('._br_user'+id,this.div).replaceWith(ht);
            else
                this.div.append(ht);
            this.nousers.hide();
            },
        onRemove: function(id){
            /*jQuery('._br_user'+id,this.div).fadeOut(); -- pretty, but unnecessarily complicated */
            jQuery('._br_user'+id,this.div).remove();
            if (!this.count)
                this.nousers.show();
            }
        },{
        type: 'privateConference',
        div: jQuery('#p2p_chat_incall'+<?php print $wuid?>),
        localVideo: document.getElementById('p2p_chat_local_video<?php print $wuid?>'),
        remoteVideo: document.getElementById('p2p_chat_remote_video<?php print $wuid?>'),
        options: {stereo: true},
        action: function(e,cmd) {
            this.control(cmd);
            return false;
            },
        adjustVideoWindows: function() {
            var   p = jQuery(this.div).parent()
                , w = p.width()
                , h = p.height()
                , o = p.offset()
                , c = jQuery('.br-controls',this.div)
                , i = jQuery('.br-indicators',this.div)
                , r = jQuery(this.remoteVideo)
                , rw = 640, rh = 480, rl = 0, rt = 0
                , l = jQuery(this.localVideo)
                , ls = (1/5)
                , lo = 10
                , lw, lh
                ;
            if ((rw/rh) > (w/h)) {
                rh = rh * (w/rw);
                rt = (h - rh)/2; 
                rw = w;
                }
            else {
                rw = rw * (h/rh);
                rl = (w - rw)/2; 
                rh = h;
                }
            lw = rw*ls; lh = rh*ls;
            r.width(rw); r.height(rh);
            r.offset({left: rl, top: rt});
            l.width(lw); l.height(lh);
            //l.offset({left: (rl+rw)-(lw+lo), top: (rt+rh)-(lh+lo)}); // -- position local within remote rect
            l.offset({left: (0+w)-(lw+lo), top: (0+h)-(lh+lo)}); // -- position local within parent rect
            c.width(w);
            c.offset({top: h-20, left: 0});
            i.width(w);
            i.offset({top: 0, left: 0});
            },
        onInit: function() {
            var _this = this;
            jQuery('.br-controls a:first-child',this.div)
                .click(function(e) { jQuery(this).hide().next().show(); return _this.action(e,'mute'); }).next()
                .click(function(e) { jQuery(this).hide().prev().show(); return _this.action(e,'unmute'); }).next()
                .click(function(e) { jQuery(this).hide().next().show(); return _this.action(e,'video_off'); }).next()
                .click(function(e) { jQuery(this).hide().prev().show(); return _this.action(e,'video_on'); }).next()
                .hide().next()  /* fullscreen */
                .hide().next()  /* exit fullscreen */
                .click(function(e) { return _this.action(e,'hangup'); })
                ;
            },
        onUserStatusUpdate: function(state, params) {
            var _this = this;
            switch(state) {
                case 'presence':
                    if (params.available)
                        jQuery('#p2p_chat_participants<?php print $wuid?> ._br_user'+params.id+'_call')
                            .show().find('a').unbind('click').click(function(){_this.call(params); return false;}).show().next().hide();
                    else
                        jQuery('#p2p_chat_participants<?php print $wuid?> ._br_user'+params.id+'_call')
                            .show().find('a').hide().next().show();
                    break;
                }
            },
        onCallStatusUpdate: function(state, params) {
            var _this = this;
            switch(state) {
                case 'calling':
                    jQuery('#p2p_chat_participants<?php print $wuid?>').hide();
                    this.div.fadeIn();
                    jQuery('.br-video,.br-ringing',this.div).hide();
                    jQuery('.br-incoming,.br-calling',this.div).show();
                    jQuery('.br-calling',this.div).html('<?php print elgg_echo('babelroom:widgets:babelroom:view:calling')?>'.replace(/%s/g,params.name))
                        .find('a.br-cancel').click(function(e) { return _this.action(e,'hangup'); });
                    jQuery('.br-msg',this.div).html(params.awaiting_permission?'<?php print elgg_echo('babelroom:widgets:babelroom:view:grant_permission')?>':'');
                    break;
                case 'ringing':
                    jQuery('#p2p_chat_participants<?php print $wuid?>').hide();
                    this.div.fadeIn();
                    jQuery('.br-video,.br-calling',this.div).hide();
                    jQuery('.br-incoming,.br-ringing',this.div).show();
                    jQuery('.br-ringing',this.div).html('<?php print elgg_echo('babelroom:widgets:babelroom:view:ringing')?>'.replace(/%s/g,params.name))
                        .find('a.br-accept').click(function(e) { return _this.action(e,'hangup'); })
                        .next(/*reject*/).click(function(e) { return _this.action(e,'hangup'); });
                    jQuery('.br-msg',this.div).html(params.awaiting_permission?'<?php print elgg_echo('babelroom:widgets:babelroom:view:grant_permission')?>':'');
                    break;
                case 'connected':
                    jQuery('.br-incoming',this.div).hide();
                    this.adjustVideoWindows();
                    jQuery('.br-video',this.div).show();
                    jQuery('.br-controls a',this.div).css('text-decoration','none');
                    jQuery('.br-controls a:first-child',this.div).show().next().hide().next().show().next().hide();
                    jQuery('.br-indicators span',this.div).hide();
                    break;
                case 'metadata':    /* channel indicators etc. */
                    var i={mute:[0,true],unmute:[0,false],video_off:[1,true],video_on:[1,false]}[params.key];
                    jQuery('.br-indicators span',this.div).eq(i[0]).toggle(i[1]);
                    break;
                case 'permission_denied':
                    jQuery('.br-msg',this.div).html("<?php print elgg_echo('babelroom:widgets:babelroom:view:permission_denied_msg') ?>");
                    break;
                case 'error':
                    jQuery('.br-msg',this.div).html("<?php print elgg_echo('babelroom:widgets:babelroom:view:error_msg') ?>");
                    break;
                case 'done':
                    setTimeout(function(){
                        jQuery('#p2p_chat_participants<?php print $wuid?>').show();
                        _this.div.fadeOut();
                        }, params.delay||0);
                    break;
                }
            },
        }]);
    _br_set_widget_title(<?php print $wuid?>, "<?php print elgg_echo('babelroom:widgets:babelroom:type:p2p_chat')?>");
})();
// ]]>
</script>






<?php






        }
    else if ($vars['entity']->babelroom_subwidget==='presentation') {
        #
        # Presentation
        #
        //echo '<div id="presentation_'.$wuid.'" style="height: '.$vars['entity']->babelroom_height.'px; overflow-y: auto; width 100%;">';
        echo '<div id="presentation_'.$wuid.'" style="width 100%; position: relative;">';
        echo '<div style="display: inline-block; width: 100%;"><center>
<span class="br-not_presenting">
    <span><a href="#" target="_blank" id="presentation_name_'.$wuid.'"></a></span>
    <span id="br_page_'.$wuid.'">--</span>
</span>
<span class="br-presenting">
    <select id="br_presentations_'.$wuid.'"><option value="-1">'.elgg_echo('babelroom:widgets:babelroom:view:select_presentation_text').'</option></select>
    <button id="br_upload_'.$wuid.'" title="Upload file...">'.elgg_echo('babelroom:widgets:babelroom:view:upload').'</button>
    <br>
    <select id="br_current_page_'.$wuid.'"><option>--</option></select>
</span>
<span>
    <span>/ </span>
    <span id="br_num_pages_'.$wuid.'">--</span>
</span>
<span class="br-presenting">
    <a href="#" id="br_beginning_'.$wuid.'" class="page_control goes_forward" title="'.elgg_echo('babelroom:widgets:babelroom:view:first').'">|&lt;</a>
    <a href="#" class="page_control goes_forward" title="'.elgg_echo('babelroom:widgets:babelroom:view:prev').'">&lt;&lt;</a>
    <a href="#" id="br_show_'.$wuid.'"  class="page_control" style="" title="'.elgg_echo('babelroom:widgets:babelroom:view:show').'">'.elgg_echo('babelroom:widgets:babelroom:view:show').'</i></a>
    <a href="#" id="br_hide_'.$wuid.'"  class="page_control" style="display: none;" title="'.elgg_echo('babelroom:widgets:babelroom:view:hide').'">'.elgg_echo('babelroom:widgets:babelroom:view:hide').'</i></a>
    <a href="#" class="page_control goes_backward" title="'.elgg_echo('babelroom:widgets:babelroom:view:next').'">&gt;&gt;</a>
    <a href="#" class="page_control goes_backward" title="'.elgg_echo('babelroom:widgets:babelroom:view:end').'">&gt;|</a>
    &nbsp;
    <a href="#" title="'.elgg_echo('babelroom:widgets:babelroom:view:close').'">'.elgg_echo('babelroom:widgets:babelroom:view:close').'</a>
</span>
<span id="br_presenter_'.$wuid.'" class="br-not_presenting">
</span>
<button id="br_make_me_presenter_'.$wuid.'" class="br-not_presenting">'.elgg_echo('babelroom:widgets:babelroom:view:make_me_presenter').'</button>
</center></div>';
        echo '<div style="height: 2px;"></div><div id="br_slide_'.$wuid.'" class="br-z-index-slide"><img id="br_slide_img_'.$wuid.'" alt="" style="cursor: crosshair; width: 100%;"></div></center>';
        echo '<div id="br_cslptr_'.$wuid.'" class="br-z-index-ptr" style="position: absolute; background-color: transparent; color: red; padding: 0; margin: 0; font-size: 2em; display: none; cursor: crosshair; pointer-events: none;">&otimes;</div>';
        echo '</div>';
        echo '
<div id="fileuploader_'.$wuid.'" style="display: none;"><iframe src="" width="640" height="360" frameborder="0"></iframe></div>
<link rel="stylesheet" href="//code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" /><!-- included solely for styling the dialog() call for file uploader -->';

/// --- for ref     <a href="#" class="page_control goes_backward" title="End">&gt;|'.elgg_view_icon('info').'</a>
?>
<script type="text/javascript">
// <![CDATA[
(function(){
    _br_singleton_instance(<?php print $cid?>).addControllers({
        type: 'presentation',
        wuid: '<?php print $wuid?>',
        div: jQuery('#presentation_'+<?php print $wuid?>),
        scaleFactor: jQuery('#presentation_'+<?php print $wuid?>).width() / 10000,
        page: jQuery('#br_page_'+<?php print $wuid?>),
        current_page: jQuery('#br_current_page_'+<?php print $wuid?>),
        num_pages: jQuery('#br_num_pages_'+<?php print $wuid?>),
        presentation_name: jQuery('#presentation_name_<?php print $wuid?>'),
        presenter: jQuery('#presenter_<?php print $wuid?>'),
        no_presentation_loaded: function() { jQuery('#presentation_name_<?php print $wuid?>').text("<?php print elgg_echo('babelroom:widgets:babelroom:view:no_presentation_loaded')?>"); },
        sel_pr: jQuery('#br_presentations_'+<?php print $wuid?>),
        enableSelect: function(sel,enable) { sel.prop('disabled',!enable); },
        selImg: jQuery('#br_slide_img_'+<?php print $wuid?>),
        selPtr: jQuery('#br_cslptr_'+<?php print $wuid?>),
        url: undefined,
        mp_url: undefined,
        ticking: undefined,
        lastPointerSendTime: 0,
        lastMMEventTime: 0,
        lastMMEvent: {},
        xyAtLastSend: {},
        pointerXY: {x:0, y:0},
        pointing: false,
        deltaXY: {x:0, y:0},
        dampFactor: 4,
        uploadDialog: function() {
            var url = this._api.get_host('cdn')+"/cdn/v1/c/ws_js/fileuploader/index.html?url="+encodeURIComponent(this._api.get_host('live')+"/upl/")+
                "&conference_id="+this._api.context.conference_id;
            var fu = jQuery('#fileuploader_'+this.wuid);
            jQuery('iframe', fu).attr("src", url+"&ts="+(new Date().getTime())+"&user_id="+this._api.context.user_id+"&csrf_token="+this._api.context.csrf_token);
            fu.dialog({modal: true, width: 'auto', title: "BabelRoom Upload"});
            //fu.show();
            },
        crop: function(name,len) {
            var pres_max = (len?len:30);
            if (name.length<=pres_max)
                return name;
            return name.substring(0,pres_max-3) + '...';
            },
        update_page_controls: function() {
            var page = parseInt(this.current_page.val(), 10) || 0;
            var num_pages = parseInt(this.num_pages.text(), 10) || 0;

            if (page>0) {
                jQuery(".page_control",this.div).prop('disabled',false);
                if (page==1)
                    jQuery(".goes_forward",this.div).prop('disabled',true);
                if (page==this.num_pages.text())
                    jQuery(".goes_backward",this.div).prop('disabled',true);
                }
            else
                jQuery(".page_control",this.div).prop('disabled',true);
            },
        presenter_set_page: function(new_page_num) {
            if (typeof(new_page_num)== 'string') {
                new_page_num = parseInt(new_page_num, 10);
                if (isNaN(new_page_num))
                    return;
                }
            if (new_page_num<1) {
                var page = parseInt(this.current_page.val(), 10);
                if (isNaN(page))
                    return;
                if (new_page_num==-1)
                    new_page_num = (page-1);
                else if (new_page_num==0)
                    new_page_num = (page+1);
                else
                    return;
                }
            var num_pages = this.num_pages.text();
            if (new_page_num<1 || new_page_num>num_pages)
                return;
            this.current_page.val(new_page_num);
            this.update_page_controls();
            if (jQuery('#br_show_'+this.wuid).css('display')=='none')
                this.changePage(new_page_num);
            },
        show_page: function(page_num) {
            // the second check here is because that file/presentation may have been deleted
            if (page_num && this.current_page.find('option[value="'+page_num+'"]').length) {
                this.current_page.val(page_num);
                this.page.text(page_num);
                jQuery('#br_show_'+this.wuid).css('display','none');
                jQuery('#br_hide_'+this.wuid).css('display','inline');
                var surl;
                if (this.mp_url.length)
                    surl = this.mp_url + '-' + page_num + '.png';
                else
                    surl = this.url;
                this.selImg.css('display','block').attr('src',surl.replace(/^https?:/,''));
                this.selPtr.css('display','block');

                jQuery(".page_control",this.div).prop('disabled',false);
                this.update_page_controls();

                if (this.ticking===undefined)
                    var _this = this;
                    this.ticking = setInterval(function() {
                        if (_this.pointing)
                            _this.point();
                        _this.deltaXY.x += (_this.pointerXY.x - _this.deltaXY.x) / _this.dampFactor;
                        _this.deltaXY.y += (_this.pointerXY.y - _this.deltaXY.y) / _this.dampFactor;
                        _this.selPtr.css({left: _this.deltaXY.x+'px', top: _this.deltaXY.y+'px'});
                        },40);
                }
            else {
                this.update_page_controls();
                jQuery('#br_show_'+this.wuid).css('display','inline');
                jQuery('#br_hide_'+this.wuid).css('display','none');
                this.selImg.css('display','none');
                this.selPtr.css('display','none');
                if (this.ticking!==undefined) {
                    clearInterval(this.ticking);
                    this.ticking = undefined;
                    }
                }
            },
        set_ptr: function(obj) {
            if (obj) {
                this.pointerXY = this.adjustOutPtr(obj);
                }
            else
                this.selPtr.css('display','none');
            },
        fnMM: function(e) {
            this.lastMMEvent = e;
            this.lastMMEvent._br_x = Math.round((this.lastMMEvent.pageX - this.selImg.offset().left) / this.scaleFactor);
            this.lastMMEvent._br_y = Math.round((this.lastMMEvent.pageY - this.selImg.offset().top) / this.scaleFactor);
            this.lastMMEventTime = (new Date).getTime();
            },
        adjustOutPtr: function(pair) {
            pair.x *= this.scaleFactor;
            pair.y *= this.scaleFactor;
            pair.x += ((this.selImg.offset().left - jQuery('#br_slide_'+this.wuid).offset().left) - (this.selPtr.width()/2)) + 1.0;
            pair.y += (this.selImg.offset().top - this.div.offset().top) - Math.ceil(this.selPtr.height()/2);
            return pair;
            },
        point: function() {
            if (!this.lastMMEventTime)
                return;
            if (this.xyAtLastSend.x==this.lastMMEvent._br_x && this.xyAtLastSend.y==this.lastMMEvent._br_y)
                return;
            else
                this.xyAtLastSend = {};

            /* give immediate feedback to presenter, comment this out to have presenter see the same thing everyone else does */
            this.pointerXY = this.adjustOutPtr({x:this.lastMMEvent._br_x, y:this.lastMMEvent._br_y});

            var now = (new Date).getTime();
            if ((now - this.lastPointerSendTime)<200)    /* too soon */
                return;

            this.setPointer(this.lastMMEvent._br_x, this.lastMMEvent._br_y);
            this.lastPointerSendTime = now;
            this.xyAtLastSend = {x:this.lastMMEvent._br_x, y:this.lastMMEvent._br_y};
            },
        presenting: function() {
            return (jQuery('.br-presenting').css('display')!=='none');
            },
        startStopPointer: function() {
            var _this = this, showingPage = (jQuery('#br_slide_'+this.wuid).html().length);
            if (this.presenting() && showingPage && !this.pointing) {
                this.pointing = true;
                this.selImg.bind('mousemove', function(e) {return _this.fnMM(e); });
                }
            if (this.pointing && !(this.presenting() && showingPage)) {
                this.pointing = false;
                this.selImg.unbind('mousemove', function(e) {return _this.fnMM(e); });
                }
            },
        onInit: function(){
            var _this = this;
            this.no_presentation_loaded();
            jQuery('#br_upload_'+this.wuid).click(function() { _this.uploadDialog(); });
            jQuery('#br_beginning_'+this.wuid)
                .click(function() { _this.presenter_set_page(1); return false; }).next()
                .click(function() { _this.presenter_set_page(-1); return false; }).next()
                .click(function() { _this.changePage(_this.current_page.val()); return false; }).next()
                .click(function() { _this.changePage(undefined); return false; }).next()
                .click(function() { _this.presenter_set_page(0); return false; }).next()
                .click(function() { _this.presenter_set_page(_this.num_pages.text()); return false; }).next()
                .click(function() { _this.close(); return false; }).next()
                ;
            jQuery('#br_make_me_presenter_<?php print $wuid?>').click(function(){ _this.makeMePresenter(); });
            this.enableSelect(this.sel_pr,false);
            this.sel_pr.change(function(){
                var selIndex = _this.sel_pr.find(':selected').val();
                _this.changePresentation(selIndex);
                });
            this.current_page.change(function(){
                _this.presenter_set_page(_this.current_page.val());
                });

            jQuery('.br-presenting',this.div).css('display','none');
            },
        onChangePage: function(page_num) {
            this.show_page(page_num);
            },
        onPresentationChange: function(obj) {
            this.show_page(0);
            this.current_page.find('option').remove();
            // the second check here is because this slide may have been deleted
            if (obj && this.sel_pr.find('option[value="'+obj.presentationIndex+'"]').length) {
                var num_pages = obj.numPages;
                this.num_pages.text(num_pages);
                if (num_pages>0) {
                    for(var i=1; i<=num_pages; i++) {
                        this.current_page.append('<option value="'+i+'">'+i+'</option>');
                        }
                    this.enableSelect(this.current_page,true);
                    }
                var presentation_name = obj.presentationName;
                this.sel_pr.val(obj.presentationIndex);
                this.url = obj.url;
                this.presentation_name.text(this.crop(presentation_name));
                this.presentation_name.attr('href',this.url);
                this.presentation_name.attr('title','Download ' + presentation_name);
                if (obj.multipage)     /* multipage */
                    this.mp_url = this.url.replace(/\.([^\/]*)\?\d*$/,'_$1');
                else
                    this.mp_url = '';
                this.update_page_controls();
                }
            else {
                this.num_pages.text("--");
                this.no_presentation_loaded();
                this.page.text('');
                this.current_page.append('<option>--</option>');
                this.enableSelect(this.current_page,false);
                this.sel_pr.val('-1');
                this.url = '';
                this.mp_url = '';
                this.update_page_controls();
                }
            },
        onPresenterChange: function(name,me) {
            if (!name.length)
                this.presenter.text('');
            else
                this.presenter.html(' <em>Presenter:</em> ' + name + ' ');
            if (name.length && me) {
                jQuery('.br-not_presenting',this.div).css('display','none');
                jQuery('.br-presenting',this.div).css('display','inline');
                }
            else {
                jQuery('.br-not_presenting',this.div).css('display','inline');
                jQuery('.br-presenting',this.div).css('display','none');
                }
            },
        onAddPresentation: function(idx, name) {
            this.sel_pr.append('<option value="'+idx+'">'+this.crop(name)+'</option>');
            this.enableSelect(this.sel_pr,true);
            },
        onRemovePresentation: function(idx) {
            this.sel_pr.find('option[value="' + idx + '"]').remove();
            },
        onSetPointer: function(obj) {
            this.set_ptr(obj);
            },
        onCheckPointer: function() {
            this.startStopPointer();
            }
        });
    _br_set_widget_title(<?php print $wuid?>, "<?php print elgg_echo('babelroom:widgets:babelroom:type:presentation')?>");
})();
// ]]>
</script>






<?php






        }
    echo "<div><center>";
    echo "$room_link";
//    echo " • ";
    echo "</center></div>";
    }
?>
