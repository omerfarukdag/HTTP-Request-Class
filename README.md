# HTTP Request Class
This class allows you to make simple HTTP requests with PHP.

## Usage
```php
require_once 'class.http.php';

$http = new Request();
$http->url('https://jsonplaceholder.typicode.com/posts')
->method('POST') // GET, POST, PUT, PATCH, DELETE
->acceptJson() // It will set the Accept header to application/json.
->bearer($token) // It will set the Authorization header to Bearer $token.
->headers([
    'Connection' => 'keep-alive',
    'Cache-Control' => 'no-cache'
]) // You can set any header here. See https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers
->settings([
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_CONNECTTIMEOUT => 30,
    CURLOPT_TIMEOUT => 30
]) // You can set any cURL option here. See http://php.net/manual/en/function.curl-setopt.php
->body([
    'userId' => 1,
    'title' => 'My first title',
    'body' => 'This is my first post.'
],true) // The body is sent as form data by default, but if you want to send it as JSON, set the second parameter to true. It will also set the Content-Type header to application/json.
->send();

if ($http->hasErrors()) {
    var_dump($http->getStatusCode());
    var_dump($http->getErrors());
} else {
    echo $http->getResponse();
}
```