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

// Database connection variables
$host = "db";
$port = "5432";
$dbname = "company_db";
$user = "user";
$password = "password";

// Connect to database
$connection = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$connection) {
    die("Connection with database failed.");
}

// Query to find all names with more than 1 occurrence and display the specific amount of occurrences
$query = "
    SELECT
        LOWER(name) AS lower_name,
        COUNT(*) AS occurrence_count
    FROM
        companies
    GROUP BY
        LOWER(name)
    HAVING
        COUNT(*) > 1;
";

$result = pg_query($connection, $query);

if (!$result) {
    die("Query failed: " . pg_last_error($connection));
}

// Display clearly in browser
echo "<pre>";
echo "Potential duplicates:\n";
echo "----------------------\n";
while ($row = pg_fetch_assoc($result)) {
    $name = $row['lower_name'];
    $occurrence_count = $row['occurrence_count'];

    // Query to find sources of current name
    $source_query = "
        SELECT
            source
        FROM
            companies
        WHERE
            LOWER(name) = LOWER($1);
    ";
    $source_result = pg_query_params($connection, $source_query, [$name]);

    if (!$source_result) {
        die("Source query failed: " . pg_last_error($connection));
    }

    $sources = [];
    while ($source_row = pg_fetch_assoc($source_result)) {
        $sources[] = $source_row['source'];
    }

    echo "Name: " . strtolower($name) . "\n";
    echo "Occurrences: " . $occurrence_count . "\n";
    echo "Sources: " . implode(", ", $sources) . "\n";
}

echo "</pre>";

// Query to insert only unique companies into the normalized_companies table with correct priority
$query = "
    INSERT INTO normalized_companies (name, canonical_website, address)
    SELECT
        LOWER(name) AS name,
        website,
        address
    FROM
        companies
    WHERE
        source = 'MANUAL'
        AND LOWER(name) NOT IN (SELECT LOWER(name) FROM normalized_companies)
    UNION
    SELECT
        LOWER(name) AS name,
        website,
        address
    FROM
        companies
    WHERE
        source = 'API'
        AND LOWER(name) NOT IN (SELECT LOWER(name) FROM companies WHERE source = 'MANUAL')
        AND LOWER(name) NOT IN (SELECT LOWER(name) FROM normalized_companies)
    UNION
    SELECT
        LOWER(name) AS name,
        website,
        address
    FROM
        companies
    WHERE
        source = 'SCRAPER'
        AND LOWER(name) NOT IN (SELECT LOWER(name) FROM companies WHERE source IN ('MANUAL', 'API'))
        AND LOWER(name) NOT IN (SELECT LOWER(name) FROM normalized_companies);
";

$result = pg_query($connection, $query);

if (!$result) {
    die("Query failed: " . pg_last_error($connection));
}

echo "Data normalized and inserted into normalized_companies.\n";

// Query to display number of companies per source, sorted in descending order
$query = "
    SELECT
        source,
        COUNT(*) AS company_count
    FROM
        companies
    GROUP BY
        source
    ORDER BY
        company_count DESC;
";

$result = pg_query($connection, $query);

if (!$result) {
    die("Query failed: " . pg_last_error($connection));
}

// Display clearly in browser
echo "<pre>";
echo "Statistics on sources:\n";
echo "----------------------\n";
while ($row = pg_fetch_assoc($result)) {
    echo "Source: " . $row['source'] . "\n";
    echo "Company Count: " . $row['company_count'] . "\n";
    echo "----------------------\n";
}
echo "</pre>";

pg_close($connection);