# How to run

1. composer update
2. composer dump-autoload **(optional)**
3. Copy .env.example and rename it to .env
4. Inside .env edit DB_DATABASE, DB_USERNAME, DB_PASSWORD and all extra info you need
5. php artisan jwt:secret
6. php artisan config:cache
7. php artisan migrate
8. php artisan db:seed --class=AdminSeeder
9. php artisan db:seed --class=ClientsSeeder **(optional)**
10. php artisan serve **or** set a virtual host.

## HTTP Responses
* 200: OK. The standard success code and default option.
* 201: Object created. Useful for the store actions.
* 204: No content. When an action was executed successfully, but there is no content to return.
* 400: Bad request. The standard option for requests that fail to pass validation.
* 401: Unauthorized. The user needs to be authenticated.
* 403: Forbidden. The user is authenticated, but does not have the permissions to perform an action.
* 404: Not found. This will be returned automatically by Laravel when the resource is not found.
* 500: Internal server error. Ideally you're not going to be explicitly returning this, but if something unexpected breaks, this is what your user is going to receive.

## To do

* Soft delete models using Laravel SoftDeletes
* Longer token TTL with Remember me
* User forgot & reset password with laravel
* Entity routes/controller/validations/model/migration examples