<?php declare(strict_types=1);
/**
 * HTTP Request Class
 * @author github/omerfarukdag
 */
class Request
{
    private CurlHandle|false $curl;
    private string|bool $response;
    private ?string $method = null;
    private array $options = [
        CURLOPT_RETURNTRANSFER => true,
    ];

    private function throwIf(bool $condition, string $message): void
    {
        if ($condition) {
            throw new Exception($message);
        }
    }

    public function __construct()
    {
        $this->throwIf(!extension_loaded('curl'), 'cURL extension is not loaded');
        $this->curl = curl_init();
    }

    public function url(string $url): self
    {
        $url = trim($url);
        $this->throwIf(!filter_var($url, FILTER_VALIDATE_URL), 'Invalid URL');
        $this->options[CURLOPT_URL] = $url;
        return $this;
    }

    public function method(string $method): self
    {
        $method = trim(strtoupper($method));
        $this->throwIf(!in_array($method, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE']), 'Invalid HTTP method');
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
            $this->throwIf(is_null($this->method), 'HTTP method is not set');
            if (in_array($this->method, ['POST', 'PUT', 'PATCH'])) {
                $this->options[CURLOPT_POSTFIELDS] = http_build_query($body);
                if ($json === true) {
                    $this->options[CURLOPT_POSTFIELDS] = json_encode($body);
                    $this->options[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';
                }
            }
            $this->throwIf(in_array($this->method, ['GET', 'DELETE']), 'GET and DELETE methods cannot have a body');
        }
        return $this;
    }

    public function acceptJson(): self
    {
        $this->options[CURLOPT_HTTPHEADER][] = 'Accept: application/json';
        return $this;
    }

    public function bearer(string $token): self
    {
        $this->options[CURLOPT_HTTPHEADER][] = 'Authorization: Bearer ' . $token;
        return $this;
    }

    public function send(): void
    {
        $this->throwIf(empty($this->options[CURLOPT_URL]), 'URL is not set');
        $this->throwIf(is_null($this->method), 'HTTP method is not set');
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

    public function hasErrors(): bool
    {
        return (bool) curl_error($this->curl);
    }

    public function getErrors(): string|bool
    {
        return curl_error($this->curl);
    }

    public function getStatusCode(): int
    {
        return curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
    }

    public function __destruct()
    {
        curl_close($this->curl);
    }
}