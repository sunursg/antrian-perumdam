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

    private function getManualPayload(): array
    {
        $payload = $this->json()->all();
        if (!empty($payload)) {
            return $payload;
        }

        $raw = (string) $this->getContent();
        if ($raw === '') {
            $raw = (string) file_get_contents('php://input');
        }

        if (empty($raw)) {
            return [];
        }

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
            return $decoded;
        }

        $parsed = [];
        parse_str($raw, $parsed);
        if (is_array($parsed) && !empty($parsed)) {
            return $parsed;
        }

        return [];
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
        
        $payload = $this->getManualPayload();
        $code = $payload['service_code'] ?? $payload['serviceCode'] ?? null;
        
        return ($code !== null && $code !== '') ? (string) $code : null;
    }

    protected function prepareForValidation(): void
    {
        if (!$this->has('service_code')) {
            $serviceCode = $this->extractServiceCode();
            if ($serviceCode !== null) {
                $this->merge(['service_code' => $serviceCode]);
            }
        }
    }

    public function validationData(): array
    {
        $data = $this->all();
        
        // Merge manual payload if standard parsing failed
        $manual = $this->getManualPayload();
        if (!empty($manual)) {
            $data = array_merge($manual, $data);
        }

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

        /*
         * Note: We use input()/all() here which relies on validationData().
         * Since we merged manual payload into validationData(), input('service_code') might NOT work 
         * if input() pulls from request source (not validationData).
         * However, standard Validator uses the data array passed to it (from validationData).
         * BUT inside rules(), $this->input() refers to Request Input, which might be empty.
         * So use $this->validationData()['service_code'] instead.
         */
        $data = $this->validationData();
        $code = $data['service_code'] ?? null;

        if ($code) {
             $service = Service::query()->where('code', $code)->first();
             if ($service && $service->requires_confirmation) {
                 $rules['confirm_service'] = ['accepted'];
             }
        }

        return $rules;
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Validation\ValidationException($validator, response()->json([
            'success' => false,
            'message' => 'Validasi gagal.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
