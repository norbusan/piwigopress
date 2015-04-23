jQuery(document).ready(function($) {
    
    function getNorrisJoke() {
        jQuery.ajax({
            url: "http://api.icndb.com/jokes/random/",
            dataType: 'jsonp',
            data: {cache: "true"},
            success: function(data) {
                jQuery('.chuck-norris-container p.joke').text(data.value.joke);
            }
        });
    }
    // getNorrisJoke(); // Run the function on page load.
    
    jQuery(document).on('click', 'a.refresh-joke', function() {
        getNorrisJoke();
        return false;
    });

    $('#jstree').on("changed.jstree", function (e, data) {
      console.log(data.selected);
    });
    jQuery(document).on('click', 'a.start-jstree', function() {
        $('#jstree').jstree();
    });
    $('button').on('click', function () {
      $('#jstree').jstree(true).select_node('child_node_1');
    });
    
    // just to remember how to insert something from this iframe!
    jQuery(document).on('click', 'a.insert-joke', function() {
        var jokeToInsert = jQuery('.chuck-norris-container p.joke').text();
        parent.wp.media.editor.insert(jokeToInsert); // This is the bit that killed me.
        // Using parent.wp enables me to use the wordpress functionality here.
        return false;
    });


    function findSubHash (p, arr) {
      //console.log (JSON.stringify(arr));
      if (arr.length == 0) {
        return(p);
      } else {
        first = arr.shift();
        if (! (first in p)) {
          p[first] = { };
        }
        return(findSubHash(p[first], arr));
      }
    }

    $('#PWGP_media_loadcat').unbind().click(function() {
      console.log("clicked on loading...");
      var url = $("#PWGP_media_finder").val(); // New URL to load
      console.log("url = " + url);
    });   /*
      // this does not work, unfortunately
      $.ajax({
        url: PwgpAjaxMedia.ajaxUrl,
        method: 'POST',
        data: {
          action: 'pwgp-categories',
          nonce: PwgpAjaxMedia.nonce,
          url: url
        },
        dataType: "json",
        success: function(data) {
        // console.log('got back: ' + data);
          var $pwgtree = $('#pwgtree');
          // console.log('loading remote categories');
          if (data.stat == 'ok') {
            var cats = data.result.categories;
            // albumdata = { title : ..., representative : .., subalbum : { NN : { }   }
            // category 0 is the main album, all others are below
            var albumdata = { };
            // console.log('response ok length ='+cats.length);
            for (var c = 0; c < cats.length; c++) {
              // work on the uppers, it is a list where the *last* element
              // is the own album id
              var upper = cats[c].uppercats.split(',');
              var targetalb = findSubHash(albumdata, upper);
              targetalb['name'] = cats[c].name;
              targetalb['id']   = cats[c].id;    //duplicated, already key in parent
              targetalb['tnurl'] = cats[c].tn_url;
              targetalb['nrimgs'] = cats[c].nb_images;
              targetalb['tonrimgs'] = cats[c].total_nb_images;
            }
            //$pwgtree.append('<ul>');
            console.log(JSON.stringify(albumdata));
          }
        },
        error: function(jqXHR, textStatus, errorThrows) {
          console.log('cannot load list of piwigo categories: ' + textStatus + ' ' + errorThrows + ' ' + jqXHR.responseText);
        }
      });
    });
*/

});
