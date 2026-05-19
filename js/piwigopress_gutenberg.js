/* PiwigoPress Gutenberg integration: adds a sidebar menu entry that opens a
   modal with the existing photo picker UI and inserts the generated
   shortcode as a core/shortcode block. */
(function (wp, $) {
  if (!wp || !wp.plugins || !wp.editPost || !wp.element || !wp.components || !wp.data || !wp.blocks) {
    return;
  }

  var el = wp.element.createElement;
  var useState = wp.element.useState;
  var useEffect = wp.element.useEffect;
  var registerPlugin = wp.plugins.registerPlugin;
  var PluginSidebarMoreMenuItem = wp.editPost.PluginSidebarMoreMenuItem;
  var Modal = wp.components.Modal;
  var __ = (wp.i18n && wp.i18n.__) ? wp.i18n.__ : function (s) { return s; };

  var pickerHtml = (window.PwgpGutenberg && window.PwgpGutenberg.pickerHtml) || '';

  function insertShortcode(scode) {
    var block = wp.blocks.createBlock('core/shortcode', { text: scode.replace(/^\s+|\s+$/g, '') });
    wp.data.dispatch('core/block-editor').insertBlocks(block);
  }

  function PickerModal(props) {
    var containerRef = wp.element.useRef(null);

    useEffect(function () {
      if (!containerRef.current) return;
      // Render the existing picker HTML and reveal it (the classic-editor
      // version is initially hidden via display:none).
      containerRef.current.innerHTML = pickerHtml;
      var $finder = $(containerRef.current).find('#PWGP_Gal_finder');
      $finder.css('display', 'block');

      // The existing jQuery in piwigopress_adm.js binds its handlers to
      // #PWGP_button, which only exists in classic editor. Trigger the same
      // initialisation by simulating that flow: copy finder html into a
      // visible container with the IDs the jQuery code expects.
      // The existing code moves #PWGP_Gal_finder into a new #PWGP_shortcoder
      // div when #PWGP_button is clicked. We replicate that here.
      if ($('#PWGP_shortcoder').length === 0) {
        var $shortcoder = $('<div id="PWGP_shortcoder" />');
        $shortcoder.html($finder.html());
        $(containerRef.current).empty().append($shortcoder);
        $finder.remove();
      }

      // Bind the same handlers the classic flow binds when #PWGP_button is
      // clicked. We re-implement the minimum needed: Load list of albums,
      // Load thumbnails, and the Gen button. The shortcode insertion is
      // intercepted via the 'piwigopress:insert' event handler below.
      bindPickerHandlers($(containerRef.current));

      var handler = function (ev) {
        ev.preventDefault();
        insertShortcode(ev.shortcode);
        props.onClose();
      };
      $(document).on('piwigopress:insert', handler);
      return function () { $(document).off('piwigopress:insert', handler); };
    }, []);

    return el(Modal, {
      title: __('Insert PiwigoPress shortcode', 'piwigopress'),
      onRequestClose: props.onClose,
      style: { maxWidth: '900px', width: '90vw' }
    }, el('div', { ref: containerRef }));
  }

  // Re-implements the wiring that piwigopress_adm.js attaches on
  // #PWGP_button click, scoped to the modal container.
  function bindPickerHandlers($root) {
    var current_catid, current_url;

    $root.find('#PWGP_catscroll').on('focus', function () {
      current_catid = this.value;
    }).on('change', function () {
      if (current_catid !== this.value) {
        $root.find('#PWGP_more, #PWGP_hide, #PWGP_show, #PWGP_show_stats').hide();
      }
    });

    $root.find('#PWGP_finder').on('focus', function () {
      current_url = this.value;
    }).on('change', function () {
      if (current_url !== this.value) {
        $root.find('#PWGP_more, #PWGP_hide, #PWGP_show, #PWGP_show_stats, #PWGP_catscroll').hide();
        $root.find('#PWGP_loadcat').show();
        $root.find('#PWGP_catscroll').val(0);
      }
    });

    $root.find('#PWGP_loadcat').off().on('click', function () {
      var url = $root.find('#PWGP_finder').val();
      $root.find('#PWGP_Load_Active').show();
      $.ajax({
        url: PwgpAjax.ajaxUrl,
        method: 'POST',
        data: { action: 'pwgp-categories', nonce: PwgpAjax.nonce, url: url },
        dataType: 'json',
        success: function (data) {
          var $sel = $root.find('#PWGP_catscroll');
          $sel.find('option[value!="0"]').remove();
          if (data.stat === 'ok') {
            for (var c = 0; c < data.result.categories.length; c++) {
              $sel.append('<option value=' + data.result.categories[c].id + '>' +
                          data.result.categories[c].name + '</option>');
            }
            $sel.focus();
          }
        }
      });
      $root.find('#PWGP_Load_Active').hide();
      $root.find('#PWGP_loadcat').hide();
      $root.find('#PWGP_catscroll').show();
    });

    $root.find('#PWGP_load').off().on('click', function () {
      var url = $root.find('#PWGP_finder').val();
      var loaded = 5;
      var $gallery = $root.find('#PWGP_dragger');
      var $trash = $root.find('#PWGP_dropping');
      $root.find('.PWGP_system').show(500);
      $root.find('#PWGP_Load_Active').show();
      var catid = $root.find('#PWGP_catscroll').val() || 0;

      $.post(PwgpAjax.ajaxUrl, {
        action: 'pwgp-thumbnails', nonce: PwgpAjax.nonce, url: url, category: catid
      }, function (response) {
        $gallery.empty().append(response);
        $root.find('#PWGP_more').show().off().on('click', getMore);
        $root.find('#PWGP_hide').show().off().on('click', function () {
          var hide = Math.max(1, Math.floor($gallery.find('li:visible').length / 2));
          for (var i = 0; i < hide; i++) $gallery.find('li:visible').first().hide();
          if ($gallery.find('li:visible').length === 0) $root.find('#PWGP_hide').hide();
          else {
            $root.find('#PWGP_show').show().off().on('click', function () {
              $gallery.find('li:hidden').show();
              $root.find('#PWGP_show').hide();
            });
          }
        });
        dragNDrop();
        $root.find('#PWGP_Load_Active').hide();

        function getMore() {
          $root.find('#PWGP_Load_Active').show();
          var c = $root.find('#PWGP_catscroll').val() || 0;
          $.ajax({
            url: PwgpAjax.ajaxUrl, method: 'POST',
            data: { action: 'pwgp-thumbnails', nonce: PwgpAjax.nonce, url: url,
                    loaded: loaded, category: c, recursive: 1 },
            success: function (html) { dragNDrop(html); }
          });
          var added = 5;
          if (loaded > 9)  added = 10;
          if (loaded > 49) added = 50;
          if (loaded > 99) added = 100;
          loaded += added;
        }

        function dragNDrop(thumbs) {
          $gallery.append(thumbs);
          var h = ($gallery.find('img').first().height()) + 20;
          $gallery.height(h);
          $trash.height(h + 25).css('min-height', h + 25);
          $trash.find('ul').height(h);
          $gallery.find('li').draggable({ revert: true, cursor: 'move', zIndex: 50 });
          var obtained = $trash.find('li').length + $gallery.find('li').length;
          $root.find('#PWGP_show_stats').show().find('#PWGP_stats').text(' ' + obtained + ' / ' + loaded);
          $trash.droppable({
            activeClass: 'ui-state-highlight',
            drop: function (event, ui) { insertImage(ui.draggable); }
          });
          if ($gallery.find('li:visible').length > 0) $root.find('#PWGP_hide').show();
          $root.find('#PWGP_Load_Active').hide();
        }

        function insertImage($item) {
          $item.fadeOut(function () {
            $item.appendTo($trash.find('ul')).fadeIn();
            $root.find('a#PWGP_Gen').off().on('click', function () {
              $root.find('#PWGP_dropping img').each(function () {
                var scode = $(this).attr('title').split(']')[0];
                var hsize = $root.find('#thumbnail_size input[name=thumbnail_size]:checked').val();
                if (hsize && hsize !== 'la') scode += " size='" + hsize + "'";
                var dn = $root.find('#display_name input[name=display_name]:checked').val();
                if (dn && dn !== '0') scode += " name='" + dn + "'";
                if ($root.find('#desc_check').is(':checked')) scode += ' desc=1';
                var hclass = $root.find('#photo_class').val();
                if (hclass) scode += " class='" + hclass + "'";
                var lnk = $root.find('#link_type input[name=link_type]:checked').val();
                if (lnk && lnk !== 'picture') scode += " lnktype='" + lnk + "'";
                var opn = $root.find('#open_type input[name=open_type]:checked').val();
                if (opn) scode += " opntype='" + opn + "'";
                scode = '[' + scode + ']';
                insertShortcode(scode);
              });
              // Close modal after insertion.
              var ev = $.Event('piwigopress:gutenberg-done');
              $(document).trigger(ev);
            });
            $root.find('a#PWGP_rst').off().on('click', function () {
              $trash.find('li').appendTo($gallery);
              $root.find('a#PWGP_Gen').hide();
              $root.find('a#PWGP_rst').hide();
            });
          });
          $root.find('a#PWGP_Gen').show();
          $root.find('a#PWGP_rst').show();
        }
      });
    });
  }

  function PiwigoPressPlugin() {
    var st = useState(false);
    var isOpen = st[0], setOpen = st[1];

    useEffect(function () {
      var h = function () { setOpen(false); };
      $(document).on('piwigopress:gutenberg-done', h);
      return function () { $(document).off('piwigopress:gutenberg-done', h); };
    }, []);

    return el(wp.element.Fragment, null,
      el(PluginSidebarMoreMenuItem, {
        icon: 'format-gallery',
        onClick: function () { setOpen(true); }
      }, __('PiwigoPress', 'piwigopress')),
      isOpen && el(PickerModal, { onClose: function () { setOpen(false); } })
    );
  }

  registerPlugin('piwigopress-gutenberg', { render: PiwigoPressPlugin });

})(window.wp, window.jQuery);
