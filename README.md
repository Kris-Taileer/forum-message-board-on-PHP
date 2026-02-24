sudo pacman -S php php-mysqli mariadb

sudo mariadb-install-db --user=mysql --basedir=/usr --datadir=/var/lib/mysql

sudo systemctl enable mariadb
sudo systemctl start mariadb

sudo mariadb -e "CREATE DATABASE myforum; USE myforum; CREATE TABLE posts (id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(50) DEFAULT 'pipis', message TEXT NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP); INSERT INTO posts (username, message) VALUES ('ADMIN', 'IT FUCKING WORKS');"

sudo mariadb -e "USE myforum; ALTER TABLE posts ADD COLUMN file_name VARCHAR(255) DEFAULT NULL, ADD COLUMN file_path VARCHAR(255) DEFAULT NULL;"

#проверить что таблица создалась: sudo mariadb -e "USE myforum; SHOW TABLES; SELECT * FROM posts;"


sudo nano /etc/php/php.ini (раскоментить extension=mysqli и extension=pdo_mysql)

php -S localhost:8000


#если чето не робит:
sudo mariadb

ALTER USER 'root'@'localhost' IDENTIFIED VIA mysql_native_password USING PASSWORD('');
FLUSH PRIVILEGES;
EXIT;
