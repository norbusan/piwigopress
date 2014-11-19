
first: css/piwigopress_adm.min.css js/piwigopress_adm.min.js

css/piwigopress_adm.min.css: css/piwigopress_adm.css
	yui-compressor css/piwigopress_adm.css -o css/piwigopress_adm.min.css


js/piwigopress_adm.min.js: js/piwigopress_adm.js
	yui-compressor js/piwigopress_adm.js -o js/piwigopress_adm.min.js

