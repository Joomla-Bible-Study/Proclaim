echo "[Installing Phing and all required dependencies]"
pear config-set auto_discover 1
pear channel-discover pear.phing.info
pear channel-discover pear.pdepend.org
pear channel-discover pear.phpmd.org
pear channel-discover pear.phpdoc.org

pear install phing/phing
pear install pdepend/PHP_Depend-beta
pear install pear.phpunit.de/PHPUnit
pear install pear.phpunit.de/phpcpd
pear install pear.phpunit.de/phploc
pear install phpunit/DbUnit
pear install phpunit/PHPUnit_Selenium
pear install phpmd/PHP_PMD
pear install PHP_CodeSniffer-1.4.4
pear install phpdoc/phpDocumentor-alpha
pear install VersionControl_Git-0.4.4

phpenv rehash

echo "[Running phing]"
phing build
