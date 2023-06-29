# University Project for the Web Development Module

For database connection you need to create a file in the project root folder with the name "credentials".
The file will be in the JSON format and should look like this:

```
{
    "database": {
          "username": "yourUsername",
          "password": "YourPassword"
      }
}
```

The main database object is created in the function `getKatzenBlogDatabase()` located in `/src/datalayer/database.php`
There is a SQL dump of the database in the file [webdev_database.sql](./webdev_database.sql) which you can import.
The website uses PDO to communicate with the database, if you choose a different DBMS than Mysql you will probably need to install the relevant PDO drivers.
