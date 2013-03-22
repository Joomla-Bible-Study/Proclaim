echo "[Installing Phing and all required dependencies]"
pear channel-discover pear.phing.info
#pear channel-discover pear.pdepend.org
pear install --alldeps phing/phing
#pear install pdepend/PHP_Depend-beta

phpenv rehash

echo "[Running phing]"
phing apidoc
