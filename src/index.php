<?php

class CompanyClass
{
    public function normalizeCompanyData(array $data): ?array
    {
        $c = [];
    
        // Return null if there is no valid name or address
        if (!$this->isCompanyDataValid($data)) {
            return null;
        }
    
        $c['name'] = strtolower(trim($data['name']));
    
        // Check whether there is website input, as this is optional
        if (!empty($data['website'])) {
            // Check whether the url starts with a protocol
            if (str_starts_with($data['website'], 'http') || (str_starts_with($data['website'], 'https')))
            {
                $protocol_url_validation_regex = "/^https?:\\/\\/(?:www\\.)?[-a-zA-Z0-9@:%._\\+~#=]{1,256}\\.[a-zA-Z0-9()]{1,6}\\b(?:[-a-zA-Z0-9()@:%_\\+.~#?&\\/=]*)$/";
                // Check whether the website matches the pattern that includes protocol 
                (preg_match($protocol_url_validation_regex, $data['website'])) && $c['website'] = parse_url($data['website'], PHP_URL_HOST);
            } else {
                $no_protocol_url_validation_regex = "/^[-a-zA-Z0-9@:%._\\+~#=]{1,256}\\.[a-zA-Z0-9()]{1,6}\\b(?:[-a-zA-Z0-9()@:%_\\+.~#?&\\/=]*)$/";
                // Check whether the website matches the pattern that excludes protocol
                (preg_match($no_protocol_url_validation_regex, $data['website'])) && $c['website'] = $data['website'];
            }
        }
    
        $c['address'] = trim($data['address']);

        return $c;
    }
  
    private function isCompanyDataValid(array $data): bool
    {
        // Check whether the keys have values and check whether those values are not empty after removing whitespace at the start and end
        return isset($data['name']) && !empty(trim($data['name']))
        && isset($data['address']) && !empty(trim($data['address']));
    }
}

// Test Data
$input = [
 'name' => ' OpenAI ',
 'website' => 'https://openai.com ',
 'address' => ' ',
];

$input2 = [
 'name' => 'Innovatiespotter',
 'address' => 'Groningen',
];

$input3 = [
 'name' => ' Apple ',
 'website' => 'xhttps://apple.com ',
];

$company = new CompanyClass();
$result = $company->normalizeCompanyData($input);

var_dump($result);

$result2 = $company->normalizeCompanyData($input2);

var_dump($result2);

$result3 = $company->normalizeCompanyData($input3);

var_dump($result3);

// Task 2
$host = "db";
$port = "5432";
$dbname = "company_db";
$user = "user";
$password = "password";

$connection = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$connection) {
    die("Connection with database failed.");
}

$result = pg_query($connection, "SELECT * FROM companies");
while ($row = pg_fetch_assoc($result)) {
    print_r($row);
}

pg_close($connection);