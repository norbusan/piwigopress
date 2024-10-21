
PACKAGE=piwigopress

all: css/piwigopress_adm.min.css js/piwigopress_adm.min.js README.md

css/piwigopress_adm.min.css: css/piwigopress_adm.css
	yui-compressor css/piwigopress_adm.css -o css/piwigopress_adm.min.css


js/piwigopress_adm.min.js: js/piwigopress_adm.js
	yui-compressor js/piwigopress_adm.js -o js/piwigopress_adm.min.js


README.md: readme.txt
	echo "CONVERSION BROKEN, DO MANUALLY"
	exit 1
	# wp2md convert -i readme.txt -o README.md

update-pot:
	xgettext				\
		--language=PHP --keyword=__ --keyword=_e	\
		--sort-by-file					\
		--copyright-holder="Norbert Preining <norbert@preining.info>" \
		--package-name=${PACKAGE}			\
		--output=languages/${PACKAGE}.pot			\
		*.php

update-po:
	for i in languages/*.po ; do \
		msgmerge -vU $$i languages/${PACKAGE}.pot ; \
		msgfmt -v -o languages/`basename $$i .po`.mo $$i ; \
	done

# the following checks whether all versions agree!
version-check:
	@NV1=`grep "^Stable tag:" readme.txt | awk -F' ' '{print $$NF}'` ;	\
	NV2=`grep "^Version:" piwigopress.php | awk -F' ' '{print $$NF}'` ; 		\
	NV3=`grep 'define(.PWGP_VERSION' piwigopress.php | sed -e "s/^.*PWGP_VERSION'\s*,\s*'//" -e "s/'.*$$//" -e 's/^\([0-9][0-9]*\.[0-9][0-9]*\)\./\1/'` ;	\
	echo "V1 = $$NV1 (readme.txt)\nV2 = $$NV2 (piwigopress.php header)\nV3 = $$NV3 (piwigopress.php variable)"; \
	if [ "$$NV1" != "$$NV2" -o "$$NV1" != "$$NV3" ] ; then false ; else true ; fi
	
