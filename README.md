# work

.htaccess прописываем:
```
RewriteEngine on
RewriteRule ^.+$ index.php [L]
```

папки
modules/branch
modules/users

импортируем базу mysql из файла test.sql

API URL:
```
branch/index/add - POST - params {name, parent_id}
branch/index/edit - POST - params {id, name, parent_id}
branch/index/delete - POST - params {id}
branch/index/all - GET - returns a text json format
branch/index/export - GET - returns a file with text json format
branch/index/import - POST - params {import} json format 

users/index/add - POST - params {name, branch_id}
users/index/edit - POST - params {id, name,branch_id}
users/index/delete - POST - params {id}
```
