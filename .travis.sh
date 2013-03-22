echo "[Installing Phing]"
pear channel-discover pear.phing.info
pear install phing/phing
phpenv rehash

echo "[Running phing]"
phing lint
