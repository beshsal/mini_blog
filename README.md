# MiniBlog

MiniBlog is a minimal yet powerful blog engine primarily built with PHP, MySQL, jQuery, and Bootstrap. While some of its features are limited, such as its static design and pages, it does contain many dynamic features and demonstrates important components and operations required in modern web development.

## Requirements
You must have a local PHP development environment installed via a web server solution stack such as MAMP, WAMP, XAMPP, or one of your choice. 

## Getting Started

These instructions will get a copy of the project up and running on your local machine for development and testing purposes.

### Installing

Initial setup

```
- Download and copy the mini_blog files to your local server

- Using a MySQL admin tool, such as phpMyAdmin, or a method of your choice, install the mini_blog database 
  (the mini_blog.sql file is located in the database folder)

- Add your database username and password credentials to the db.inc.php file located in the includes folder

```

Creating the first admin user

```
- Click on the "MENU" button

- Click on the "Register here" link and register a new user (this creates a user with a role of "member")

- Using a MySQL admin tool, such as phpMyAdmin, or a method of your choice, access the users table and 
  change the user's role to "admin"

After completing these steps, you will be able to access the admin interface using your username and
password and create other admin and author users.

```


Setting up the Mailtrap test SMTP server

```
- Create a Mailtrap account (https://mailtrap.io/)

- Click on Demo Inbox under My Inboxes

- Add the provided credentials (host, port, username, and password) to the Config.php file located in 
  includes/classes

```

## Built With

* [PHP](http://www.php.net/)
* [MySQL](https://www.mysql.com/)
* [jQuery](https://jquery.com/)
* [Bootstrap 3](https://getbootstrap.com/docs/3.3/)

Other Tools
* [Bower](https://bower.io/)
* [Composer](https://getcomposer.org/)
* [PHPMailer](https://github.com/PHPMailer)

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details.

# MiniBlog
