<?php

class CompanyClass
{
    public function normalizeCompanyData(array $data): ?array
    {
        $c = [];
    
        if (!$this->isCompanyDataValid($data)) {
            return;
        }
    
        $c['name'] = strtolower(trim($data['name']));
    
        (preg_match('/http?:\/\//i', $cleanWebsite))
            ? $c['website'] = parse_url($data['website'], PHP_URL_HOST)
            : $c['website'] = $data['website'];
    
    
        if ($c['website'] == null) {
            unset($c['website']);
        }
    
        if (isset($data['address']))
            $c['address'] = trim($data['address']);
  
        if (empty($c['address'])) {
            $c['address'] = null;
        }

        return $c;
    }
  
    private function isCompanyDataValid(array $data): bool
    {
      
        return isset($data[0]) && isset($data['address']);
    }
}

// Test Data
$input = [
 'name' => ' OpenAI ',
 'website' => 'https://openai.com ',
 'address' => ' '
];

$input2 = [
 'name' => 'Innovatiespotter',
 'address' => 'Groningen'
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