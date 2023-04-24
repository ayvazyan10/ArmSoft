## ArmSoft Laravel Package
This Laravel package allows you to interact with the ArmSoft API easily, providing methods for authentication and
working with various endpoints such as Goods, GoodsRem, PriceList, DocumentsJournal, and MTBill.

- Authenticate and refresh access token
- Get goods, goodsRems, price lists, documents journal and MTBill from ArmSoft API
- Send data for creating an invoice

#### Notes:
- This package is currently in beta version.
- The ArmSoft API is still in development and may change without notice.
- I will update the package as soon as they give me more information.

[![Buy me a coffee](https://img.shields.io/badge/Buy%20me%20a%20coffee-Donate-yellow?style=for-the-badge&logo=buymeacoffee)](https://www.buymeacoffee.com/ayvazyan403)

![Image Description](https://wedo.design/logo-black.svg)

### 🚀 Installation

#### You can install the package using Composer:

```` bash
composer require ayvazyan10/armsoft
````

#### Release the configuration file and database migration.
First, you need to configure the package with your ArmSoft API credentials. You can do this by publishing the configuration file:
```` bash
php artisan vendor:publish --provider="Ayvazyan10\ArmSoft\ArmSoftServiceProvider" --tag="config"
````
Then, fill in the config/armsoft.php file with your ArmSoft API credentials and settings.

### ⚙️ Configuration

After publishing the configuration file, you should set your ArmSoft credentials/options in the config/armsoft.php file
or in your .env file:

- ARM_SOFT_CLIENT_ID - Your ArmSoft API client ID.
- ARM_SOFT_SECRET - Your ArmSoft API secret key.
- ARM_SOFT_DB_ID - The ID of your ArmSoft database.

```` ini
ARM_SOFT_CLIENT_ID=your_client_id
ARM_SOFT_SECRET=your_secret
ARM_SOFT_DB_ID=your_db_id
````
or provide it in config/armsoft.php
```` php
/**
 * Configuration for the ArmSoft API package.
 *
 * Note: This package is currently in beta version and the ArmSoft API is not yet finished.
 */

return [
    // Required: Your ArmSoft client ID
    'clientId' => env('ARM_SOFT_CLIENT_ID', '00000000-0000-0000-0000-000000000000'),

    // Required: Your ArmSoft client secret
    'secret' => env('ARM_SOFT_SECRET', '000000000000'),

    // Required: The ID of your ArmSoft database
    'dbId' => env('ARM_SOFT_DB_ID', '00000'),

    // Optional: The price type to use for price-related API calls (01 - wholesale, 02 - retail, 03 - purchase price)
    'priceType' => '02',

    // Optional: The language to use in API responses
    'language' => 'en-US,en;q=0.5',

    // Optional: Additional settings to pass to the ArmSoft API
    'settings' => [
        'ShowProgress' => false,
        'ShowColumns' => false,
    ],
];
````

### ⚡ All Methods

Please note that this only contains method signatures without the implementation details.

```` php
// getGoods
final public function getGoods(string $date = null): ?array { /*...*/ }

// getGoodsRem
final public function getGoodsRem(string $date = null, string $mtcode = null): ?array { /*...*/ }

// getPrices
final public function getPrices(string $date = null, string $mtcode = null, string $pricetypes = null): ?array { /*...*/ }

// getDocumentsJournal
final public function getDocumentsJournal(string $dateBegin = null, string $dateEnd = null): ?array { /*...*/ }

// getMTbill
final public function getMTbill(mixed $guid): mixed { /*...*/ }

// setMTbill
final public function setMTbill(array $data): mixed { /*...*/ }
````

### 📚 Usage

Here is an example of how to use the ArmSoft facade or helper in your Laravel application:

```` php
use ayvazyan10\ArmSoft\Facades\ArmSoft;

// with facade
$goods = ArmSoft::getGoods('2023-04-24');

// or use the helper function
$goods = armsoft()->getGoods('2023-04-24');

// or ArmSoft class directly 
use ayvazyan10\ArmSoft\ArmSoft;

$armSoft = new ArmSoft();
$goods = $armSoft->getGoods('2023-04-24');
````

### 📖 Examples & Explanations

Below are some examples of how you could use these methods with a Facade:<br><br>
In this example, Armsoft is a Facade that allows you to access the Armsoft API using the methods provided by the
Armsoft class. The Facade is registered in the app.php config file and bound to the Armsoft class using the
Laravel service container.

``` php
// In your controller or anywhere else
use ayvazyan10\ArmSoft\Facades\ArmSoft;

public function myMethod()
{
    // Get the goods for today
    $goods = Armsoft::getGoods();

    // Get the goods rem for a specific MTCode
    $goodsRem = Armsoft::getGoodsRem(null, 'MTCode123');

    // Get the prices for a specific date and MTCode
    $prices = Armsoft::getPrices('2023-04-24', 'MTCode123');

    // Get the documents journal for a date range
    $documentsJournal = Armsoft::getDocumentsJournal('2023-04-01', '2023-04-30');

    // Get an MTBill by its GUID
    $mtBill = Armsoft::getMTbill('MTBill123');

    // Create an MTBill with some data
    $newMTBill = Armsoft::setMTbill([
        'MTCode' => 'MTCode123',
        'Description' => 'Some description',
        // ...
    ]);
}
```
You can use the Facade to call the getGoods(), getGoodsRem(), getPrices(), getDocumentsJournal(), getMTbill(), and
setMTbill() methods provided by the Armsoft class. You can pass in any necessary parameters to these methods, as
shown in the example above. If an error occurs while calling the API, the methods will throw an Exception with a
descriptive error message.

### Example with products import
```` php
    // in your controller or anywhere else
    // in this example we using helper
    /**
     * Products Import from ArmSoft API.
     *
     * @throws Exception
     */
    public function importGoods()
    {
        $items = armsoft()->getGoods(now()->format('Y-m-d'));

        if (count($items["rows"]) === 0) {
            throw new Exception('ArmSoft API error: no products');
        }

        foreach ($items["rows"] as $key => $item) {
            if (((int)$key + 1) % 2 === 0) {
                continue;
            }

            $priceRequest = armsoft()->getPrices(null, $item["MTCode"], config('armsoft.PriceType', '02'));
            $price = !empty(current($priceRequest["rows"])["Price"]) ? current($priceRequest["rows"])["Price"] : 0;

            $stockRequest = armsoft()->getGoodsRem(null, $item["MTCode"]);
            $stock = !empty(current($stockRequest["rows"])["Qty"]) ? current($stockRequest["rows"])["Qty"] : 0;

            $productData = [
                'armsoft_title' => $item["FullCaption"]    
                'category_id' => $item["Group"],
                'MTCode' => $item["MTCode"],
                'discount' => $item["Discount"],
                'stock' => intval($stock),
                'price' => $price
                // ... fields
            ];

            try {
                YourModel::where('MTCode', $item["MTCode"])->updateOrCreate([
                    'MTCode' => $item["MTCode"]
                ], $productData);
            } catch (Exception $e) {
                return $e->getMessage();
            }
        }

        return redirect()->back();
    }
````

### Detailed explanation of each method:

getGoods(string $date = null): ?array: This method returns the goods with the given RemDate. You can pass in a date
string in yyyy-mm-dd format to retrieve goods for a specific date. If no date is provided, the method will retrieve
goods for the current date. The method returns an array of goods, or null if no goods are found.
getGoodsRem(string $date = null, string $mtcode = null): ?array: This method returns the GoodsRem with the given RemDate
and single one if MTCode provided. You can pass in a date string in yyyy-mm-dd format to retrieve goods rem for a
specific date. If no date is provided, the method will retrieve goods rem for the current date. You can also pass in a
unique MTCode to retrieve a specific GoodsRem. The method returns an array of GoodsRem, or null if no GoodsRem are
found.

getPrices(string $date = null, string $mtcode = null, string $pricetypes = null): ?array: This method returns the
PriceList with the given RemDate and single one if MTCode|PriceType provided. You can pass in a date string in
yyyy-mm-dd format to retrieve prices for a specific date. If no date is provided, the method will retrieve prices for
the current date. You can also pass in a unique MTCode and/or price types to retrieve specific prices. The method
returns an array of prices, or null if no prices are found.

getDocumentsJournal(string $dateBegin = null, string $dateEnd = null): ?array: This method returns the DocumentsJournal
with the given RemDate and single one if MTCode|PriceType provided. You can pass in a date range to retrieve documents
for a specific range. If no date range is provided, the method will retrieve all documents. The method returns an array
of documents, or null if no documents are found.

getMTbill(mixed $guid): mixed: This method returns an MTBill with the given GUID. You can pass in a GUID string to
retrieve a specific MTBill. The method returns an MTBill object, or null if no MTBill is found.

setMTbill(array $data): mixed: This method sends MTBill data for creating an invoice. You can pass in an array of MTBill
data to create a new invoice. The method returns the created MTBill object, or null if the MTBill could not be created.

### All of these methods may throw an Exception if an error occurs while calling the ArmSoft API.
#### You can catch it like this
```` php
// in your controller method or anywhere else
try {
    $mtbill = ArmSoft::getMTbill('your_guid_here');
} catch (Exception $e) {
    return $e->getMessage();
}
// Perform actions with $mtbill or $response data
````

## Contributing

Please see [contributing.md](contributing.md) for details and a todolist.

## Security

If you discover any security related issues, please email ayvazyan403@gmail.com instead of using the issue tracker.

## Author

- <a href="https://github.com/ayvazyan10">Razmik Ayvazyan</a>

## License

MIT. Please see the [license file](license.md) for more information.
