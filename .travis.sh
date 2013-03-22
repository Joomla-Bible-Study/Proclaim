echo "[Installing Phing and all required dependencies]"
pear config-set auto_discover 1
pear channel-discover pear.phing.info
pear channel-discover pear.pdepend.org
pear channel-discover pear.phpmd.org
pear channel-discover pear.phpdoc.org

pear install phing/phing
pear install pdepend/PHP_Depend-beta
pear install pear.phpunit.de/PHPUnit
pear install phpmd/PHP_PMD
pear install PHP_CodeSniffer-1.5.0RC1
pear install pear.phpunit.de/phpcpd
pear install pear.phpunit.de/phploc
pear install phpdoc/phpDocumentor-alpha

phpenv rehash

echo "[Running phing]"
phing package
