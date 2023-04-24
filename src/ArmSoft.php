<?php

namespace Ayvazyan10\ArmSoft;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Http;

class ArmSoft
{
    private const AUTH_ENDPOINT = 'https://dbservices.armsoft.am/mobiletrade/api/Login/ApiKey';
    private const GOODS_ENDPOINT = 'https://dbservices.armsoft.am/mobiletrade/api/Data/Goods';
    private const GOODSREM_ENDPOINT = 'https://dbservices.armsoft.am/mobiletrade/api/Data/GoodsRems';
    private const PRICE_ENDPOINT = 'https://dbservices.armsoft.am/mobiletrade/api/Data/PriceList';
    private const MTBILL_ENDPOINT = 'https://dbservices.armsoft.am/mobiletrade/api/Documents/MTBill';
    private const DOCUMENT_JOURNAL_ENDPOINT = 'https://dbservices.armsoft.am/mobiletrade/api/DocumentsJournal';

    private mixed $accessToken;
    private mixed $accessTokenExpiresAt;

    protected string $date;

    /**
     * Constructor.
     * @throws Exception
     */
    public function __construct()
    {
        $this->accessToken = session('access_token');
        $this->accessTokenExpiresAt = session('access_token_expires_at');

        if (!$this->isAccessTokenValid()) {
            $this->refreshAccessToken();
        }

        $this->date = Carbon::now()->format('Y-m-d');
    }

    /**
     * Checks if the access token is still valid.
     *
     * @return bool True if the access token is valid, false otherwise.
     */
    final protected function isAccessTokenValid(): bool
    {
        return $this->accessToken && $this->accessTokenExpiresAt && Carbon::now()->lt($this->accessTokenExpiresAt);
    }

    /**
     * Refreshes the access token by calling the ArmSoft API.
     *
     * @throws Exception If an error occurs while calling the ArmSoft API.
     */
    final protected function refreshAccessToken(): void
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json'
        ])
            ->post(self::AUTH_ENDPOINT, [
                'ClientId' => config('armsoft.ClientId'),
                'Secret' => config('armsoft.Secret'),
                'DBId' => config('armsoft.DBId')
            ]);

        if ($response->successful()) {
            $data = json_decode($response->body());
            $this->accessToken = $data->accessToken;
            $expiresInMinutes = 3600 / 60; // Convert seconds to minutes
            $this->accessTokenExpiresAt = Carbon::now()->addMinutes($expiresInMinutes);

            // Store the access token and its expiration time in the session
            session()->put('access_token', $this->accessToken);
            session()->put('access_token_expires_at', $this->accessTokenExpiresAt);
        } else {
            throw new Exception('ArmSoft API authentication error: ' . $response->getReasonPhrase().'/'.$response->getStatusCode(), $response->getStatusCode());
        }
    }

    /**
     * Returns the Goods with the given RemDate.
     *
     * @param string|null $date The RemDate in yyyy-mm-dd format.
     * @return array|null The Goods, or null if not found.
     * @throws Exception If an error occurs while calling the ArmSoft API.
     */
    final public function getGoods(string $date = null): ?array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->accessToken,
            'Accept-Language' => config('armsoft.language', 'hy-AM,hy;q=0.5'),
            'Content-Type' => 'application/json'
        ])
            ->post(self::GOODS_ENDPOINT, [
                'settings' => config('armsoft.settings'),
                'parameters' => [
                    'RemDate' => $date ?? Carbon::now()->format('Y-m-d')
                ]
            ]);

        if ($response->successful()) {
            return json_decode($response->body(), true);
        } else {
            throw new Exception('ArmSoft API error: ' . $response->getReasonPhrase().'/'.$response->getStatusCode(), $response->getStatusCode());
        }
    }

    /**
     * Returns the GoodsRem with the given RemDate and single one if MTCode provided.
     *
     * @param string|null $date The RemDate in yyyy-mm-dd format.
     * @param string|null $mtcode The unique MTCode of object.
     * @return array|null The GoodsRem, or null if not found.
     * @throws Exception If an error occurs while calling the ArmSoft API.
     */
    final public function getGoodsRem(string $date = null, string $mtcode = null): ?array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->accessToken,
            'Accept-Language' => config('armsoft.language', 'hy-AM,hy;q=0.5'),
            'Content-Type' => 'application/json'
        ])
            ->post(self::GOODSREM_ENDPOINT, [
                'settings' => config('armsoft.settings'),
                'parameters' => [
                    'RemDate' => $date ?? Carbon::now()->format('Y-m-d'),
                    'MTCode' => $mtcode
                ]
            ]);

        if ($response->successful()) {
            return json_decode($response->body(), true);
        } else {
            throw new Exception('ArmSoft API error: ' . $response->getReasonPhrase().'/'.$response->getStatusCode(), $response->getStatusCode());
        }
    }

    /**
     * Returns the PriceList with the given RemDate and single one if MTCode|PriceType provided.
     *
     * @param string|null $date The RemDate in yyyy-mm-dd format.
     * @param string|null $mtcode The unique MTCode of object.
     * @param string|null $pricetypes ID of price types. Example:
     * @return array|null The PriceList, or null if not found.
     * @throws Exception If an error occurs while calling the ArmSoft API.
     */
    final public function getPrices(string $date = null, string $mtcode = null, string $pricetypes = null): ?array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->accessToken,
            'Accept-Language' => config('armsoft.language', 'hy-AM,hy;q=0.5'),
            'Content-Type' => 'application/json'
        ])
            ->post(self::PRICE_ENDPOINT, [
                'settings' => config('armsoft.settings'),
                'parameters' => [
                    'Date' => $date ?? Carbon::now()->format('Y-m-d'),
                    'MTCode' => $mtcode,
                    "PriceTypes" => $pricetypes
                ]
            ]);

        if ($response->successful()) {
            return json_decode($response->body(), true);
        } else {
            throw new Exception('ArmSoft API error: ' . $response->getReasonPhrase().'/'.$response->getStatusCode(), $response->getStatusCode());
        }
    }

    /**
     * Returns the DocumentsJournal with the given RemDate and single one if MTCode|PriceType provided.
     *
     * @param string|null $dateBegin The DateBegin in yyyy-mm-dd format
     * @param string|null $dateEnd The DateEnd in yyyy-mm-dd format
     * @return array|null The DocumentsJournal, or null if not found.
     * @throws Exception If an error occurs while calling the ArmSoft API.
     */
    final public function getDocumentsJournal(string $dateBegin = null, string $dateEnd = null,): ?array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->accessToken,
            'Accept-Language' => config('armsoft.language', 'hy-AM,hy;q=0.5'),
            'Content-Type' => 'application/json'
        ])
            ->post(self::DOCUMENT_JOURNAL_ENDPOINT, [
                'settings' => config('armsoft.settings'),
                'parameters' => [
                    "DateBegin" => $dateBegin,
                    "DateEnd" => $dateEnd
                ]
            ]);

        if ($response->successful()) {
            return json_decode($response->body(), true);
        } else {
            throw new Exception('ArmSoft API error: ' . $response->getReasonPhrase().'/'.$response->getStatusCode(), $response->getStatusCode());
        }
    }

    /**
     * Returns MTBill with the given guid.
     *
     * @return mixed|null The MTBill, or null if not found.
     * @throws Exception If an error occurs while calling the ArmSoft API.
     */
    final public function getMTbill(mixed $guid): mixed
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->accessToken,
            'Accept-Language' => config('armsoft.language', 'hy-AM,hy;q=0.5'),
            'Content-Type' => 'application/json'
        ])
            ->get(self::MTBILL_ENDPOINT, [
                'guid' => $guid,
            ]);

        if ($response->successful()) {
            return json_decode($response->body(), true);
        } else {
            throw new Exception('ArmSoft API error: ' . $response->getReasonPhrase().'/'.$response->getStatusCode(), $response->getStatusCode());
        }
    }

    /**
     * Sending MTBill the data for creating an invoice.
     *
     * @return mixed|null The MTBill, or null if not found.
     * @throws Exception If an error occurs while calling the ArmSoft API.
     */
    final public function setMTbill(array $data): mixed
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->accessToken,
            'Accept-Language' => config('armsoft.language', 'hy-AM,hy;q=0.5'),
            'Content-Type' => 'application/json'
        ])
            ->post(self::MTBILL_ENDPOINT, $data);

        if ($response->successful()) {
            return json_decode($response->body(), true);
        } else {
            throw new Exception('ArmSoft API error: ' . $response->getReasonPhrase().'/'.$response->getStatusCode(), $response->getStatusCode());
        }
    }
}
