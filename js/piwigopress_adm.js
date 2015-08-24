/*
Compiler on http://refresh-sf.com/yui/
*/
(function($){
  $.fn.extend({
    insertAtCaret: function(myValue){ // Insert at Cursor position within HTML editor
      return this.each(function(i) {
      if (document.selection) {
        //For browsers like Internet Explorer
        this.focus();
        sel = document.selection.createRange();
        sel.text = myValue;
        this.focus();
      }
      else if (this.selectionStart || this.selectionStart == '0') {
        //For browsers like Firefox and Webkit based
        var startPos = this.selectionStart;
        var endPos = this.selectionEnd;
        var scrollTop = this.scrollTop;
        this.value = this.value.substring(0, startPos)+myValue+this.value.substring(endPos,this.value.length);
        this.focus();
        this.selectionStart = startPos + myValue.length;
        this.selectionEnd = startPos + myValue.length;
        this.scrollTop = scrollTop;
      } else {
        this.value += myValue;
        this.focus();
      }
      })
    }
  });
  $(document).ready(function() {
    var pwgp_Gallery_Display = true;
    $("a#PWGP_button").unbind().click(function () {
      if ( pwgp_Gallery_Display ) {
        if ( $('#dashboard-widgets-wrap').size() == 0 ) { var where = "#poststuff"; }
        else { var where = "#dashboard-widgets-wrap"; } // On Dashboard
        if ( $("#PWGP_shortcoder").size() == 0 ) { // First: Create Drag & Drop zones
          var finder = $("#PWGP_Gal_finder").html();
          $( where ).before('<div id="PWGP_shortcoder" />');
          $("#PWGP_shortcoder").html(finder);
          $("#PWGP_Gal_finder").remove();
        } else { // Just show Drag & Drop zones
          $("#PWGP_shortcoder").show();
        }
        $('#PWGP_catscroll').on('focus', function () {
          current_catid = this.value;
	  // console.log('entering catscroll on function');
        }).change(function() { // changing the catid hides buttons
	  // console.log('entering catscroll change function');
          if (current_catid != this.value) {
            $("#PWGP_more").hide();
            $("#PWGP_hide").hide();
            $("#PWGP_show").hide();
            $("#PWGP_show_stats").hide();
          }
        });
        $('#PWGP_finder').focus(function() { // Changing Gallery URL hides buttons
	  current_url = this.value;
	  // console.log('jumped into foxus in ');
	}).change(function() {
	  // console.log('entering url change function');
	  if (current_url != this.value) {
            $("#PWGP_more").hide();
            $("#PWGP_hide").hide();
            $("#PWGP_show").hide();
            $("#PWGP_show_stats").hide();
            $("#PWGP_catscroll").hide();
            $('#PWGP_loadcat').show();
            $('#PWGP_catscroll').val(0);
	  }
        });
        $('#PWGP_loadcat').unbind().click(function() {
          var url = $("#PWGP_finder").val(); // New URL to load
          $('#PWGP_Load_Active').show(); // Busy icon is on

          $.ajax({
            url: PwgpAjax.ajaxUrl,
            method: 'POST',
            data: {
              action: 'pwgp-categories',
              nonce: PwgpAjax.nonce,
              url: url
            },
            dataType: "json",
            success: function(data) {
	      // console.log('got back: ' + data);
              var $dropper = $('#PWGP_catscroll');
              // remove all but the 0 value which is translated!
              $('#PWGP_catscroll option[value!="0"]').remove();
              // console.log('loading remote categories');
              if (data.stat == 'ok') {
                var cats = data.result.categories;
                // console.log('response ok length ='+cats.length);
                for (var c = 0; c < cats.length; c++) {
                  var nm = cats[c].name;
                  var id = cats[c].id;
                  $dropper.append('<option value='+id+'>'+nm+'</option>');
                }
                $dropper.select();
                $dropper.focus();
              }
            },
            error: function(jqXHR, textStatus, errorThrows) {
              console.log('cannot load list of piwigo categories: ' + textStatus + ' ' + errorThrows + ' ' + jqXHR.responseText);
            }
          });
          $('#PWGP_Load_Active').hide();
          $('#PWGP_loadcat').hide();
          $('#PWGP_catscroll').show();
        });
        $("#PWGP_load").unbind().click(function () {
          var url = $("#PWGP_finder").val(), // New URL to load
              loaded = 5,
              $gallery = $( "#PWGP_dragger" ),
              $dragli = $( "#PWGP_dragger li" ),
              $trash = $( "#PWGP_dropping" );
          $('.PWGP_system').show(500);

          $('#PWGP_Load_Active').show(); // Busy icon is on

          // Ready to Load and generate
          // TODO norbert that somehow does not worek???
          var catid = $('#PWGP_catscroll').val();
          // console.log('catid='+catid);
          if (!catid) { catid = 0; }

          $.post(PwgpAjax.ajaxUrl, {
            action: 'pwgp-thumbnails',
            nonce: PwgpAjax.nonce,
            url: url,
            category: catid
          }, function(response) {
            $gallery.empty().append(response);
            $("#PWGP_more").show().unbind().click(function () {
              Get_more();
            });
            $("#PWGP_hide").show().unbind().click(function () {
              var hide = Math.max(1, Math.floor( $('li:visible', $gallery).size() / 2 ));
              for(i=0;i<hide;i++) {
                $gallery.find('li:visible').first().hide();
              }
              if ($('li:visible', $gallery).size() == 0) $("#PWGP_hide").hide();
              else {
                $("#PWGP_show").show().unbind().click(function () {
                  $('li:hidden', $gallery).show();
                  $("#PWGP_show").hide();
                });
              }
            });
            Drag_n_Drop();
            $('#PWGP_Load_Active').hide(); // Busy icon is off
            
            function Get_more() {
              $('#PWGP_Load_Active').show();
              catid = $('#PWGP_catscroll').val();
              if (!catid) { catid = 0; }
              // here we need to add the category id if set to the argument list, for now only category
              // and recursive is also necessary!!
              $.ajax({
                url: PwgpAjax.ajaxUrl,
                data: {
                  action: 'pwgp-thumbnails',
                  nonce: PwgpAjax.nonce,
                  url: url,
                  loaded: loaded,
                  category: catid,
                  recursive: 1
                },
                method: 'post',
                success: function(html){
                  Drag_n_Drop(html);
                }
              });
              var added = 5;
              if (loaded > 9) added = 10; 
              if (loaded > 49) added = 50; 
              if (loaded > 99) added = 100; // More we load larger next load might be
              loaded += added;
            }
            function Drag_n_Drop(thumbs) {
              $($gallery).append(thumbs);
              var hgal = ($('#PWGP_dragger img').first().height())+20;  
              $gallery.height(hgal); // Ajust loading area height to thumbnail height
              $trash.height(hgal+25).css('min-height', hgal+25); // Adjust dropping zone as well
              $('#PWGP_dropping ul').height(hgal);
              $('li', $gallery).draggable({
                revert: true, cursor: "move", zIndex: 50
              });
              var obtained = $('li', $trash).size() + $('li', $gallery).size();
              $("#PWGP_show_stats").show().find("#PWGP_stats").text(' '+obtained+' / '+loaded);
              $trash.droppable({
                activeClass: "ui-state-highlight",
                drop: function( event, ui ) { 
                  insertImage( ui.draggable ); // This DOM is now droppable
                }
              });
              if ($('li:visible', $gallery).size() > 0) $("#PWGP_hide").show();
              $('#PWGP_Load_Active').hide();
            }
            function insertImage( $item ) {
              $item.fadeOut(function() {
                var $list = $( "ul", $trash );
                $item.appendTo( $list ).fadeIn(); // Available to be shortcoded
                $("a#PWGP_Gen").unbind().click(function () {
                  var tmce_ver=window.tinyMCE.majorVersion;
                  $("img", $trash).each(function () {
                    var $Shortcode = $(this).attr('title').split(']');
                    var $scode = $Shortcode[0];
                    var $hsize = $('#thumbnail_size input[type=radio][name=thumbnail_size]:checked').attr('value');
                    if ( $hsize !== 'la') $scode += " size='"+$hsize+"'";
                    var $dispname = $('#display_name input[type=radio][name=display_name]:checked').attr('value');
                    if ( $dispname !== '0') $scode += " name='"+$dispname+"'";
                    $('input#desc_check[name=desc_check]').attr('value',0);
                    $hdesc = 0 + $('input#desc_check[name=desc_check]:checked').attr('value',1).attr('value');
                    if ( $hdesc == 1 ) $scode += " desc=1";
                    var $hclass = $('#photo_class').val();
                    if ( $hclass != '' ) $scode += " class='"+$hclass+"'";
                    var $lnktype = $('#link_type input[type=radio][name=link_type]:checked').attr('value');
                    if ( $lnktype != 'picture' ) $scode += " lnktype='"+$lnktype+"'";
                    var $opntype = $('#open_type input[type=radio][name=open_type]:checked').attr('value');
                    if ( $opntype != '' ) $scode += " opntype='"+$opntype+"'";
                    var $ordertype = $('#order_type input[type=radio][name=order_type]:checked').attr('value');
                    if ( $ordertype != '' ) $scode += " order='"+$ordertype+"'";

                    var $scode = "\t"+$scode+"] \n\r";

                    // HTML Editor only insert statement
                    $('#content').insertAtCaret( $scode );
                    // Visual Editor Only insert statement
                    if (tmce_ver>="4") {
                      tinyMCE.execCommand('mceInsertContent', false, $scode);
                    } else {
                      tinyMCE.execInstanceCommand('content', 'mceInsertContent', false, $scode);
                    }

                    // If you are using another WordPress Post Editor 
                    // and if you already found its right insert statement, 
                    // please, for all PiwigoPress users, share it through a dedicate topic there: 
                    // http://wordpress.org/support/plugin/piwigopress
                  });  
                });
                $("a#PWGP_rst").unbind().click(function () {
                  $('li', $trash).appendTo( $gallery );
                  $("a#PWGP_Gen").hide();
                  $("a#PWGP_rst").hide();
                  $("#PWGP_show_stats").show().find("#PWGP_stats").text(' '+$('li', $gallery).size()+' / '+loaded);
                });
              }); 
              $("a#PWGP_Gen").show();
              $("a#PWGP_rst").show();
            }
          }); // End of Loaded
        });  
        pwgp_Gallery_Display = false;
      } else {
        $("div#PWGP_shortcoder").hide();
        pwgp_Gallery_Display = true;
      }
    });
  });
}(jQuery));

/* vim:set expandtab tabstop=2 shiftwidth=2 autoindent smartindent: */
