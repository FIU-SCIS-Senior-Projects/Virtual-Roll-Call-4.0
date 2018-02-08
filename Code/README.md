Code Directory Structure

- Database
 - VIRTUAL_ROLL_CALL.sql: contains SQL code to create Database, tables, and insert data into the mysql database for VRC ver.2.0
 * Make sure to edit the connection details in the DBHandler.php file after to setting up the database

- Website
 - app
  - controllers: These are the angular js controllers for the site.
  - php: API php files, DB Handler class, and uploads directory for document uploads.
  - services: The data service file is responsible for all data operations and http requests.
  - vendor: Angular core and additional modules are included here, as well as the ng-flow library for the logo uploads functionality.
  - app.js: Defines the angular applications and associated routes.
 - views
  - img: Images used in the views are stored here, including the site logo.
  - partials: Used with Angular's ng-view, used in conjunction with Angular routes to produce a single page application.
  - style: All the css files for the web application.
  - admin-profile.html: Base admin html file
  - supervisor-profile.html: Base supervisor html file
  - officer-profile.html: Base officer html file