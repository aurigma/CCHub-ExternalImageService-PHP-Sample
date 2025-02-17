# External Image Server Php Sample
Software for demonstration of work with user's photos and obtaining preliminary images.


# Installation process 
To set up this project, ensure you have the following installed:

PHP 7.4

MySQL Server 8.0

After installing PHP, enable the following extensions in your php.ini file (you can copy from php.ini-development if needed):

```
extension=curl
extension=fileinfo
extension=mbstring
extension=mysqli
extension=openssl
extension=pdo_mysql
```

Additionally, for Windows systems, download the cacert.pem certificate from this link: https://curl.se/docs/caextract.html and specify its path in the php.ini file:

curl.cainfo = ‘C:/<YOUR_PATH>/cacert.pem’.


# Software dependencies

Install project dependencies using Composer:

composer install


# Environment Configuration

Create the .env file by running:

cp .env.example .env

Then, set the following parameters in your .env file:

```
CC_HUB_API_URL=<YOUR_API_URL_FROM_CCHUB>
CC_HUB_CLIENT_ID=<YOUR_CLIENT_ID_FROM_CCHUB>
CC_HUB_CLIENT_SECRET=<YOUR_CLIENT_SECRET_FROM_CCHUB>
```


# Generating the JWT Secret

Run the following command to generate the JWT secret key:

php artisan jwt:secret

By default, the token remains active for 60 minutes. You can modify this duration by changing the JWT_TTL parameter in the .env file.


# Database Setup

To connect to your database, update the .env file with your database credentials:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=<YOUR_DATABASE_NAME>
DB_USERNAME=<YOUR_USERNAME>
DB_PASSWORD=<YOUR_PASSWORD>
```

Then, run the following command to migrate the database:

php artisan migrate

After a successful migration, your database should contain tables including users and file_infos, which store user data and file information.


# Running the Application

To start the project, use the following command:

php artisan serve

This will run the application on a local server. Ensure that ports 8000 (for the Laravel server) and 3306 (for MySQL) are not in use by other applications.

To test the API endpoints, refer to the route definitions in routes/api.php and prepend each route with:

http://localhost:8000/api


# Authentication & Token Management

Register a New User
To register a new user, send a POST request to:

http://localhost:8000/api/auth/registration

```
Method: POST
Request Body (form-data):
name = <your_value>
email = <your_value> (must be unique)
password = <your_value>
All attributes should be sent as text.
```

Obtain an Authentication Token
To obtain an authentication token, log in with a registered user by sending a POST request to:

http://localhost:8000/api/auth/login

```
Method: POST
Request Body (form-data):
email = <your_value> (must match a registered email)
password = <your_value>
All attributes should be sent as text.
```

After a successful login, the response will contain a JWT token, which should be included in the Authorization header for subsequent API requests:

Authorization: Bearer <your_token>
