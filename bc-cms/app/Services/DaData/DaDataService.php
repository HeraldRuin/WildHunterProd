<?php

namespace App\Services\DaData;

use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use RuntimeException;

class DaDataService
{
    public function findPartyByInn(string $inn): ?array
    {
        $inn = preg_replace('/\D+/', '', $inn) ?? '';

        if (!preg_match('/^\d{10}$|^\d{12}$/', $inn)) {
            throw new InvalidArgumentException(__('Invalid INN'));
        }

        $token = config('services.dadata.token');
        if (empty($token)) {
            throw new RuntimeException(__('DaData is not configured'));
        }

        $baseUrl = rtrim((string) config('services.dadata.base_url'), '/');
        $timeout = (int) config('services.dadata.timeout', 10);

        $response = Http::timeout($timeout)
            ->withHeaders([
                'Authorization' => 'Token ' . $token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->post($baseUrl . '/findById/party', [
                'query' => $inn,
            ]);

        if (!$response->successful()) {
            throw new RuntimeException(__('DaData request failed'));
        }

        $suggestions = $response->json('suggestions');
        if (!is_array($suggestions) || empty($suggestions[0])) {
            return null;
        }

        return $this->mapPartySuggestion($suggestions[0]);
    }

    protected function mapPartySuggestion(array $suggestion): array
    {
        $data = $suggestion['data'] ?? [];
        $name = $data['name'] ?? [];
        $opf = $data['opf'] ?? [];
        $address = $data['address'] ?? [];

        $legalEntity = $suggestion['value']
            ?? ($name['short_with_opf'] ?? null)
            ?? ($name['full_with_opf'] ?? null)
            ?? ($name['short'] ?? null)
            ?? ($name['full'] ?? '');

        $ownershipForm = $opf['full'] ?? ($opf['short'] ?? '');
        $requisites = $address['unrestricted_value']
            ?? ($address['value'] ?? '');

        return [
            'legal_entity' => (string) $legalEntity,
            'legal_inn' => (string) ($data['inn'] ?? ''),
            'legal_ogrn' => (string) ($data['ogrn'] ?? ''),
            'legal_ownership_form' => (string) $ownershipForm,
            'legal_requisites' => (string) $requisites,
        ];
    }
}
