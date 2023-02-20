# HTTP Request Class
This class allows you to make simple HTTP requests with PHP.

## Usage
```php
require_once 'class.http.php';

$http = new Http();
$http->url('http://127.0.0.1:8000/api/v1/posts')
->method('POST') // GET, POST, PUT, PATCH, DELETE
->json() // It will set the Accept header to application/json.
->bearer($token) // It will set the Authorization header to Bearer $token.
->headers([
    'Cache-Control' => 'no-cache'
]) // You can set any header here.
->settings([
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_CONNECTTIMEOUT => 30,
    CURLOPT_TIMEOUT => 30
]) // You can set any cURL option here.
->body([
    'title' => 'My first title',
    'body' => 'This is my first post.'
],true) // The body is sent as form data by default, but if you want to send it as JSON, set the second parameter to true. It will also set the Content-Type header to application/json.
->exec();

if ($http->hasError()) {
    echo($http->getError());
} else {
    echo($http->getResponse());
}
```