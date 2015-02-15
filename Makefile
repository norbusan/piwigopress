
PACKAGE=piwigopress

all: css/piwigopress_adm.min.css js/piwigopress_adm.min.js README.md

css/piwigopress_adm.min.css: css/piwigopress_adm.css
	yui-compressor css/piwigopress_adm.css -o css/piwigopress_adm.min.css


js/piwigopress_adm.min.js: js/piwigopress_adm.js
	yui-compressor js/piwigopress_adm.js -o js/piwigopress_adm.min.js


README.md: readme.txt
	/usr/local/wp2md/vendor/bin/wp2md convert -i readme.txt -o README.md

update-pot:
	xgettext				\
		--language=PHP --keyword=__ --keyword=_e	\
		--sort-by-file					\
		--copyright-holder="Norbert Preining <norbert@preining.info>" \
		--package-name=${PACKAGE}			\
		--output=languages/pwg.pot			\
		*.php

