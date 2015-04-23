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

});
