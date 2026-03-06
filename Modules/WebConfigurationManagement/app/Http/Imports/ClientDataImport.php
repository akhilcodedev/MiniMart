<?php

namespace Modules\WebConfigurationManagement\Imports;

use App\Models\MapProvider;
use Google\Service\AdMob\App;
use Modules\UserManagement\Models\Country;
use Modules\UserManagement\Models\Language;
use Modules\UserManagement\Models\Currency;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\TimeZone;
use Modules\WebConfigurationManagement\Models\TimeZone;
use PhpOffice\PhpSpreadsheet\Reader\Csv;

class ClientDataImport implements ToModel, WithHeadingRow
{
    protected string $type;

    /**
     * Constructor to initialize type (currency, language, country)
     *
     * @param string $type
     */
    public function __construct(string $type)
    {
        $this->type = $type;
    }

    /**
     * @param array $row
     *
     * @return Country|Currency|Language|null
     * @throws \Exception
     */
    public function model(array $row)
    {
        switch ($this->type) {
            case 'currency':
                $this->validateCurrencyRow($row);

                return Currency::updateOrCreate(
                    ['iso_code' => $row['iso_code'] ?? null],
                    [
                        'name' => $row['name'] ?? null,
                        'symbol' => $row['symbol'] ?? null,
                        'subunit' => $row['subunit'] ?? null,
                        'subunit_to_unit' => $row['subunit_to_unit'] ?? null,
                        'symbol_first' => $row['symbol_first'] ?? null,
                        'html_entity' => $row['html_entity'] ?? null,
                        'decimal_mark' => $row['decimal_mark'] ?? null,
                        'thousands_separator' => $row['thousands_separator'] ?? null,
                        'iso_numeric' => $row['iso_numeric'] ?? null,
                    ]
                );

            case 'language':
                $this->validateLanguageRow($row);

                return Language::updateOrCreate(
                    ['sort_code' => $row['sort_code']],
                    [
                        'name' => $row['name'],
                        'native_name' => $row['native_name'] ?? null,
                    ]
                );

            case 'country':
                $this->validateCountryRow($row);

                return Country::updateOrCreate(
                    ['code' => $row['code'] ?? null],
                    [
                        'name' => $row['name'] ?? null,
                        'nicename' => $row['nicename'] ?? null,
                        'iso3' => $row['iso3'] ?? null,
                        'numcode' => $row['numcode'] ?? null,
                        'phonecode' => $row['phonecode'] ?? null,
                    ]
                );

            case 'timezone':
                $this->validateTimezoneRow($row);

                return Timezone::updateOrCreate(
                    ['id' => $row['id'] ?? null],
                    [
                        'timezone' => $row['timezone'] ?? null,
                        'offset' => $row['offset'] ?? null,
                        'diff_from_gtm' => $row['diff_from_gtm'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );


            case 'map_providers':
                $this->validateMapProvider($row);

                return MapProvider::updateOrCreate(
                    ['id' => $row['id'] ?? null],
                    [
                        'provider' => $row['provider'] ?? null,   // Add provider
                        'keyword' => $row['keyword'] ?? null,     // Add keyword
                        'status' => $row['status'] ?? null,       // Add status
                        'created_at' => now(), // Use row value or current time
                        'updated_at' => now(), // Always update to the current time
                    ]
                );


            default:
                throw new \Exception('Invalid type specified.');
        }
    }



    private function validateMapProvider(array $row): void
    {
        if (!isset($row['id']) || !isset($row['provider']) || !isset($row['keyword'])) {
            throw new \Exception('Missing required columns in map provider data: ' . json_encode($row));
        }
    }

    /**
     * Validate the currency row data.
     *
     * @param array $row
     * @throws \Exception
     */
    private function validateCurrencyRow(array $row): void
    {
        if (!isset($row['iso_code']) || !isset($row['name']) || !isset($row['symbol'])) {
            throw new \Exception('Missing required columns in currency data: ' . json_encode($row));
        }
    }

    /**
     * Validate the language row data.
     *
     * @param array $row
     * @throws \Exception
     */
    private function validateLanguageRow(array $row): void
    {
        if (!isset($row['sort_code']) || !isset($row['name'])) {
            throw new \Exception('Missing required columns in language data: ' . json_encode($row));
        }
    }

    /**
     * Validate the country row data.
     *
     * @param array $row
     * @throws \Exception
     */
    private function validateCountryRow(array $row): void
    {
        if (!isset($row['code']) || !isset($row['name'])) {
            throw new \Exception('Missing required columns in country data: ' . json_encode($row));
        }
    }

    /**
     * Validate the country row data.
     *
     * @param array $row
     * @throws \Exception
     */

    private function validateTimezoneRow(array $row): void
    {
        if (!isset($row['id']) || !isset($row['timezone']) || !isset($row['offset']) || !isset($row['diff_from_gtm'])) {
            throw new \Exception('Missing required columns in timezone data: ' . json_encode($row));
        }
    }


}
