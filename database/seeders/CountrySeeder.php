<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            ['Algeria', 'DZ', 'Northern Africa'], ['Angola', 'AO', 'Middle Africa'], ['Benin', 'BJ', 'Western Africa'], ['Botswana', 'BW', 'Southern Africa'], ['Burkina Faso', 'BF', 'Western Africa'], ['Burundi', 'BI', 'Eastern Africa'], ['Cabo Verde', 'CV', 'Western Africa'], ['Cameroon', 'CM', 'Middle Africa'], ['Central African Republic', 'CF', 'Middle Africa'], ['Chad', 'TD', 'Middle Africa'], ['Comoros', 'KM', 'Eastern Africa'], ['Congo', 'CG', 'Middle Africa'], ['Côte d’Ivoire', 'CI', 'Western Africa'], ['Democratic Republic of the Congo', 'CD', 'Middle Africa'], ['Djibouti', 'DJ', 'Eastern Africa'], ['Egypt', 'EG', 'Northern Africa'], ['Equatorial Guinea', 'GQ', 'Middle Africa'], ['Eritrea', 'ER', 'Eastern Africa'], ['Eswatini', 'SZ', 'Southern Africa'], ['Ethiopia', 'ET', 'Eastern Africa'], ['Gabon', 'GA', 'Middle Africa'], ['Gambia', 'GM', 'Western Africa'], ['Ghana', 'GH', 'Western Africa'], ['Guinea', 'GN', 'Western Africa'], ['Guinea-Bissau', 'GW', 'Western Africa'], ['Kenya', 'KE', 'Eastern Africa'], ['Lesotho', 'LS', 'Southern Africa'], ['Liberia', 'LR', 'Western Africa'], ['Libya', 'LY', 'Northern Africa'], ['Madagascar', 'MG', 'Eastern Africa'], ['Malawi', 'MW', 'Eastern Africa'], ['Mali', 'ML', 'Western Africa'], ['Mauritania', 'MR', 'Western Africa'], ['Mauritius', 'MU', 'Eastern Africa'], ['Morocco', 'MA', 'Northern Africa'], ['Mozambique', 'MZ', 'Eastern Africa'], ['Namibia', 'NA', 'Southern Africa'], ['Niger', 'NE', 'Western Africa'], ['Nigeria', 'NG', 'Western Africa'], ['Rwanda', 'RW', 'Eastern Africa'], ['São Tomé and Príncipe', 'ST', 'Middle Africa'], ['Senegal', 'SN', 'Western Africa'], ['Seychelles', 'SC', 'Eastern Africa'], ['Sierra Leone', 'SL', 'Western Africa'], ['Somalia', 'SO', 'Eastern Africa'], ['South Africa', 'ZA', 'Southern Africa'], ['South Sudan', 'SS', 'Eastern Africa'], ['Sudan', 'SD', 'Northern Africa'], ['Tanzania', 'TZ', 'Eastern Africa'], ['Togo', 'TG', 'Western Africa'], ['Tunisia', 'TN', 'Northern Africa'], ['Uganda', 'UG', 'Eastern Africa'], ['Western Sahara', 'EH', 'Northern Africa'], ['Zambia', 'ZM', 'Eastern Africa'], ['Zimbabwe', 'ZW', 'Eastern Africa']];
        foreach ($countries as [$name,$iso,$region]) {
            Country::updateOrCreate(['iso_code' => $iso], ['name' => $name, 'slug' => Str::slug($name), 'region' => $region, 'is_african' => true]);
        }
    }
}
