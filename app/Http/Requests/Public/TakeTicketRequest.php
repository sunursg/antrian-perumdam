<?php

namespace App\Http\Requests\Public;

use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;

class TakeTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    private function extractServiceCode(): ?string
    {
        $routeCode = $this->route('service_code') ?? $this->route('serviceCode');

        if ($routeCode !== null && $routeCode !== '') {
            return is_string($routeCode) ? $routeCode : (string) $routeCode;
        }

        $direct = $this->input('service_code')
            ?? $this->input('serviceCode')
            ?? $this->query('service_code')
            ?? $this->query('serviceCode');

        if ($direct !== null && $direct !== '') {
            return is_string($direct) ? $direct : (string) $direct;
        }

        $payload = $this->json()->all();

        $raw = '';
        if (empty($payload)) {
            $raw = (string) $this->getContent();
            if ($raw === '') {
                $raw = (string) file_get_contents('php://input');
            }
        }

        if (!empty($raw)) {
            $raw = trim($raw);
            $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw) ?? $raw;

            if (strlen($raw) >= 2) {
                $first = $raw[0];
                $last = $raw[strlen($raw) - 1];
                if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                    $raw = substr($raw, 1, -1);
                }
            }

            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $payload = $decoded;
            }

            if (!is_array($payload)) {
                $parsed = [];
                parse_str($raw, $parsed);
                if (is_array($parsed) && !empty($parsed)) {
                    $payload = $parsed;
                }
            }

            if (!is_array($payload)) {
                if (preg_match('/"service_code"\s*:\s*"([^"]+)"/i', $raw, $m)) {
                    return $m[1];
                }
                if (preg_match("/'service_code'\\s*:\\s*'([^']+)'/i", $raw, $m)) {
                    return $m[1];
                }
                if (preg_match('/service_code\\s*[:=]\\s*([A-Za-z0-9_-]+)/i', $raw, $m)) {
                    return $m[1];
                }
            }
        }

        if (!is_array($payload)) {
            return null;
        }

        $serviceCode = $payload['service_code'] ?? $payload['serviceCode'] ?? null;
        if ($serviceCode === null || $serviceCode === '') {
            return null;
        }

        return is_string($serviceCode) ? $serviceCode : (string) $serviceCode;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('service_code')) {
            return;
        }

        $serviceCode = $this->extractServiceCode();
        if ($serviceCode !== null) {
            $this->merge(['service_code' => $serviceCode]);
        }
    }

    public function validationData(): array
    {
        $data = $this->all();

        if (!array_key_exists('service_code', $data)) {
            $serviceCode = $this->extractServiceCode();
            if ($serviceCode !== null) {
                $data['service_code'] = $serviceCode;
            }
        }

        return $data;
    }

    public function rules(): array
    {
        $rules = [
            'service_code' => ['required','string','max:10','exists:services,code'],
        ];

        $service = Service::query()->where('code', $this->input('service_code'))->first();
        if ($service && $service->requires_confirmation) {
            $rules['confirm_service'] = ['accepted'];
        }

        return $rules;
    }
}
