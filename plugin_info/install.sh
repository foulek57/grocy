
apt-get install -y php-sqlite3
a2enmod proxy proxy_http rewrite
systemctl restart apache2

wget -O /tmp/grocy.zip https://releases.grocy.info/latest
mkdir /var/www/html/plugins/grocy/3rdparty/grocy
unzip /tmp/grocy.zip -d /var/www/html/plugins/grocy/3rdparty/grocy

cp /var/www/html/plugins/grocy/3rdparty/grocy/config-dist.php /var/www/html/plugins/grocy/3rdparty/grocy/data/config.php 
chown -R www-data:www-data  /var/www/html/plugins/grocy/3rdparty/grocy