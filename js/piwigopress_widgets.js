	jQuery(document).ready(function() { // Select a larger sized picture (additional radio inputs within the Widget menu
		jQuery(".PWGP_widget a._more").click(function () {
			jQuery(this).parent("label").siblings("span.hidden").removeClass("hidden");
			jQuery(this).parent("label").hide();
		});
		jQuery(".PWGP_widget").parents(".widget").find("a.widget-action").click(function () {
			jQuery(this).parents(".widget").toggleClass( "pwgp_shadow" );
		});
	});
	function all_selectable() {
		var checked_status = this.checked;
		jQuery(this).parents("fieldset").find("input").each(function() {
			this.checked = checked_status;
		}); 
		jQuery(this).parents("label").find("span").toggle();
	};

	jQuery(".PWGP_widget input.MenuSel").on("click", all_selectable );
	jQuery(".PWGP_widget").parents(".widget").ajaxComplete(function() {
		jQuery(".PWGP_widget input.MenuSel").on("click", all_selectable );
		jQuery(".PWGP_widget a._more").click(function () {
			jQuery(this).parent("label").siblings("span.hidden").removeClass("hidden");
			jQuery(this).parent("label").hide();
		});
	});