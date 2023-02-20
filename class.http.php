<?php
/**
 * HTTP Request Class
 * @author Ömer Faruk DAĞ https://github.com/omerfarukdag
 */
class Http
{
    private CurlHandle|false $curl;
    private string|bool $response;
    private string $method;
    private array $options = [
        CURLOPT_RETURNTRANSFER => true,
    ];

    public function __construct()
    {
        if (!extension_loaded('curl')) {
            throw new Exception('cURL extension is not loaded');
        } else {
            $this->curl = curl_init();
        }
    }

    public function url(string $url): self
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new Exception('Invalid URL');
        }
        $this->options[CURLOPT_URL] = $url;
        return $this;
    }

    public function method(string $method): self
    {
        if (!in_array($method, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'])) {
            throw new Exception('Invalid HTTP method');
        }
        $this->method = $method;
        return $this;
    }

    public function headers(array $headers): self
    {
        if (!empty($headers)) {
            foreach ($headers as $header) {
                $this->options[CURLOPT_HTTPHEADER][] = $header;
            }
        }
        return $this;
    }

    public function settings(array $options): self
    {
        if (!empty($options)) {
            foreach ($options as $key => $value) {
                $this->options[$key] = $value;
            }
        }
        return $this;
    }

    public function body(array $body, bool $json = false): self
    {
        if (!empty($body)) {
            if (!isset($this->method)) {
                throw new Exception('HTTP method is not set');
            }
            if (in_array($this->method, ['POST', 'PUT', 'PATCH'])) {
                $this->options[CURLOPT_POSTFIELDS] = (true === $json) ? json_encode($body) : http_build_query($body);
                if (true === $json) {
                    $this->options[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';
                }
            } elseif (in_array($this->method, ['GET', 'DELETE'])) {
                throw new Exception('GET and DELETE methods cannot have a body');
            }
        }
        return $this;
    }

    public function json(): self
    {
        $this->options[CURLOPT_HTTPHEADER][] = 'Accept: application/json';
        return $this;
    }

    public function bearer(string $token): self
    {
        $this->options[CURLOPT_HTTPHEADER][] = 'Authorization: Bearer ' . $token;
        return $this;
    }

    public function exec(): void
    {
        if (!isset($this->options[CURLOPT_URL])) {
            throw new Exception('URL is not set');
        }
        if (!isset($this->method)) {
            throw new Exception('HTTP method is not set');
        }
        if ($this->method === 'POST') {
            $this->options[CURLOPT_POST] = true;
        } elseif ($this->method !== 'GET') {
            $this->options[CURLOPT_CUSTOMREQUEST] = $this->method;
        }
        curl_setopt_array($this->curl, $this->options);
        $this->response = curl_exec($this->curl);
    }

    public function getResponse(): string|bool
    {
        return $this->response;
    }

    public function hasError(): bool
    {
        return (bool) curl_error($this->curl);
    }

    public function getError(): string|bool
    {
        return curl_error($this->curl);
    }

    // public function getStatusCode(): int
    // {
    //     return curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
    // }

    // public function getOptions(): array
    // {
    //     return $this->options;
    // }

    // public function getVersion(): array
    // {
    //     return curl_version();
    // }

    public function __destruct()
    {
        curl_close($this->curl);
    }
}