<?php

declare(strict_types=1);

namespace App\Services;

class WebScraper
{
    private const TIMEOUT = 10;
    private const MAX_SIZE = 2 * 1024 * 1024; // 2MB
    private const USER_AGENT = 'Mozilla/5.0 (compatible; FastClient CRM/1.0)';

    private const PRIVATE_RANGES = [
        '10.0.0.0/8',
        '172.16.0.0/12',
        '192.168.0.0/16',
        '127.0.0.0/8',
        '169.254.0.0/16',
        '0.0.0.0/8',
        '100.64.0.0/10',
        '::1/128',
        'fc00::/7',
        'fe80::/10',
    ];

    private const INDUSTRY_MAP = [
        'Restaurant' => 'Restaurant',
        'FoodEstablishment' => 'Restaurant',
        'CafeOrCoffeeShop' => 'Restaurant',
        'BarOrPub' => 'Restaurant',
        'Bakery' => 'Restaurant',
        'AutoDealer' => 'Automotive',
        'AutoRepair' => 'Automotive',
        'AutoPartsStore' => 'Automotive',
        'Attorney' => 'Legal',
        'LegalService' => 'Legal',
        'MedicalBusiness' => 'Healthcare',
        'Dentist' => 'Healthcare',
        'Physician' => 'Healthcare',
        'Hospital' => 'Healthcare',
        'Pharmacy' => 'Healthcare',
        'VeterinaryCare' => 'Healthcare',
        'RealEstateAgent' => 'Real Estate',
        'InsuranceAgency' => 'Insurance',
        'FinancialService' => 'Finance',
        'AccountingService' => 'Finance',
        'EducationalOrganization' => 'Education',
        'School' => 'Education',
        'Store' => 'Retail',
        'ClothingStore' => 'Retail',
        'HardwareStore' => 'Retail',
        'ElectronicsStore' => 'Retail',
        'GroceryStore' => 'Retail',
        'HomeAndConstructionBusiness' => 'Construction',
        'Plumber' => 'Construction',
        'Electrician' => 'Construction',
        'RoofingContractor' => 'Construction',
        'HVACBusiness' => 'Construction',
        'LodgingBusiness' => 'Hospitality',
        'Hotel' => 'Hospitality',
        'SportsActivityLocation' => 'Fitness',
        'HealthAndBeautyBusiness' => 'Beauty',
        'HairSalon' => 'Beauty',
        'DaySpa' => 'Beauty',
        'TravelAgency' => 'Travel',
        'ProfessionalService' => 'Professional Services',
    ];

    private const INDUSTRY_KEYWORDS = [
        'restaurant' => 'Restaurant',
        'plumbing' => 'Construction',
        'hvac' => 'Construction',
        'roofing' => 'Construction',
        'dental' => 'Healthcare',
        'medical' => 'Healthcare',
        'clinic' => 'Healthcare',
        'law firm' => 'Legal',
        'attorney' => 'Legal',
        'real estate' => 'Real Estate',
        'realty' => 'Real Estate',
        'insurance' => 'Insurance',
        'salon' => 'Beauty',
        'spa' => 'Beauty',
        'fitness' => 'Fitness',
        'gym' => 'Fitness',
        'auto' => 'Automotive',
        'car dealer' => 'Automotive',
        'hotel' => 'Hospitality',
        'motel' => 'Hospitality',
        'accounting' => 'Finance',
        'consulting' => 'Professional Services',
        'technology' => 'Technology',
        'software' => 'Technology',
        'marketing' => 'Marketing',
    ];

    /**
     * @return array{success: bool, data?: array{name?: string, email?: string, phone?: string, city?: string, state?: string, industry?: string}, error?: string}
     */
    public function scrape(string $url): array
    {
        $url = $this->normalizeUrl($url);

        if (!$this->isValidUrl($url)) {
            return ['success' => false, 'error' => 'Invalid URL format.'];
        }

        if ($this->isPrivateAddress($url)) {
            return ['success' => false, 'error' => 'URL points to a private/internal address.'];
        }

        $html = $this->fetch($url);

        if ($html === null) {
            return ['success' => false, 'error' => 'Could not fetch the website. It may be unreachable or blocking requests.'];
        }

        $data = $this->extract($html);

        return ['success' => true, 'data' => $data];
    }

    private function normalizeUrl(string $url): string
    {
        $url = trim($url);

        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . $url;
        }

        return $url;
    }

    private function isValidUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false
            && preg_match('#^https?://#i', $url) === 1;
    }

    private function isPrivateAddress(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);
        if (!$host) {
            return true;
        }

        // Resolve hostname to IP
        $ip = gethostbyname($host);
        if ($ip === $host) {
            // Could not resolve — might be an IP already or unresolvable
            $ip = $host;
        }

        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return true;
        }

        foreach (self::PRIVATE_RANGES as $range) {
            if (str_contains($range, ':')) {
                continue; // Skip IPv6 for IPv4 addresses
            }
            if ($this->ipInRange($ip, $range)) {
                return true;
            }
        }

        return false;
    }

    private function ipInRange(string $ip, string $range): bool
    {
        if (!str_contains($range, '/')) {
            return $ip === $range;
        }

        [$subnet, $bits] = explode('/', $range);
        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);
        $mask = -1 << (32 - (int) $bits);

        return ($ipLong & $mask) === ($subnetLong & $mask);
    }

    private function fetch(string $url): ?string
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => self::TIMEOUT,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_USERAGENT => self::USER_AGENT,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_ENCODING => '', // Accept any encoding
            CURLOPT_HTTPHEADER => [
                'Accept: text/html,application/xhtml+xml',
                'Accept-Language: en-US,en;q=0.9',
            ],
        ]);

        // Limit download size
        $data = '';
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $chunk) use (&$data) {
            $data .= $chunk;
            if (strlen($data) > self::MAX_SIZE) {
                return 0; // Abort
            }
            return strlen($chunk);
        });

        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode < 200 || $httpCode >= 400 || empty($data)) {
            return null;
        }

        return $data;
    }

    /**
     * @return array{name?: string, email?: string, phone?: string, city?: string, state?: string, industry?: string}
     */
    private function extract(string $html): array
    {
        $data = [];

        // First try JSON-LD (highest quality structured data)
        $jsonLd = $this->extractJsonLd($html);

        if ($jsonLd) {
            if (!empty($jsonLd['name'])) {
                $data['name'] = $jsonLd['name'];
            }
            if (!empty($jsonLd['email'])) {
                $data['email'] = $jsonLd['email'];
            }
            if (!empty($jsonLd['telephone'])) {
                $data['phone'] = $jsonLd['telephone'];
            }
            if (!empty($jsonLd['address']['addressLocality'])) {
                $data['city'] = $jsonLd['address']['addressLocality'];
            }
            if (!empty($jsonLd['address']['addressRegion'])) {
                $data['state'] = $jsonLd['address']['addressRegion'];
            }
            if (!empty($jsonLd['@type'])) {
                $type = is_array($jsonLd['@type']) ? $jsonLd['@type'][0] : $jsonLd['@type'];
                if (isset(self::INDUSTRY_MAP[$type])) {
                    $data['industry'] = self::INDUSTRY_MAP[$type];
                }
            }
        }

        // Suppress HTML parsing errors
        libxml_use_internal_errors(true);
        $doc = new \DOMDocument();
        $doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'), LIBXML_NOERROR);
        $xpath = new \DOMXPath($doc);
        libxml_clear_errors();

        // Fill gaps with meta tags / Open Graph
        if (empty($data['name'])) {
            $data['name'] = $this->extractMeta($xpath, 'og:site_name')
                ?? $this->extractMeta($xpath, 'og:title')
                ?? $this->extractTitle($xpath);
        }

        // Email fallback: mailto links then regex
        if (empty($data['email'])) {
            $data['email'] = $this->extractMailto($xpath)
                ?? $this->extractEmailRegex($html);
        }

        // Phone fallback: tel links then regex
        if (empty($data['phone'])) {
            $data['phone'] = $this->extractTelLink($xpath)
                ?? $this->extractPhoneRegex($html);
        }

        // Industry fallback: meta keywords
        if (empty($data['industry'])) {
            $data['industry'] = $this->extractIndustryFromKeywords($xpath, $html);
        }

        // Clean up: remove nulls and trim values
        return array_filter(
            array_map(fn($v) => is_string($v) ? trim($v) : $v, $data),
            fn($v) => $v !== null && $v !== ''
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    private function extractJsonLd(string $html): ?array
    {
        if (!preg_match_all('#<script[^>]*type=["\']application/ld\+json["\'][^>]*>(.*?)</script>#si', $html, $matches)) {
            return null;
        }

        $orgTypes = ['Organization', 'LocalBusiness', 'Corporation', 'Store',
            'Restaurant', 'MedicalBusiness', 'LegalService', 'FinancialService',
            'RealEstateAgent', 'AutoDealer', 'AutoRepair', 'EducationalOrganization',
            'ProfessionalService', 'HomeAndConstructionBusiness', 'LodgingBusiness',
            'SportsActivityLocation', 'HealthAndBeautyBusiness', 'HairSalon',
            'FoodEstablishment', 'Dentist', 'Physician', 'Attorney', 'Plumber',
            'Electrician', 'RoofingContractor', 'HVACBusiness', 'InsuranceAgency',
            'AccountingService', 'TravelAgency'];

        foreach ($matches[1] as $json) {
            $decoded = json_decode(trim($json), true);
            if (!$decoded) {
                continue;
            }

            // Handle @graph wrapper
            $items = isset($decoded['@graph']) ? $decoded['@graph'] : [$decoded];

            foreach ($items as $item) {
                $type = $item['@type'] ?? '';
                $types = is_array($type) ? $type : [$type];

                foreach ($types as $t) {
                    if (in_array($t, $orgTypes, true)) {
                        return $item;
                    }
                }
            }
        }

        return null;
    }

    private function extractMeta(\DOMXPath $xpath, string $property): ?string
    {
        $node = $xpath->query("//meta[@property='{$property}']/@content")->item(0)
            ?? $xpath->query("//meta[@name='{$property}']/@content")->item(0);

        $value = $node?->nodeValue;
        return ($value !== null && $value !== '') ? $value : null;
    }

    private function extractTitle(\DOMXPath $xpath): ?string
    {
        $node = $xpath->query('//title')->item(0);
        if (!$node) {
            return null;
        }

        $title = $node->textContent;

        // Clean up common title patterns: "Company Name | Tagline", "Company - Description"
        $title = preg_replace('/\s*[|\-–—]\s*.+$/', '', $title);

        return trim($title) ?: null;
    }

    private function extractMailto(\DOMXPath $xpath): ?string
    {
        $nodes = $xpath->query("//a[starts-with(@href, 'mailto:')]/@href");

        for ($i = 0; $i < $nodes->length; $i++) {
            $href = $nodes->item($i)->nodeValue;
            $email = str_replace('mailto:', '', $href);
            $email = strtok($email, '?'); // Remove query params

            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $email;
            }
        }

        return null;
    }

    private function extractEmailRegex(string $html): ?string
    {
        // Strip scripts and styles to avoid false positives
        $clean = preg_replace('#<(script|style)[^>]*>.*?</\1>#si', '', $html);

        if (preg_match('/[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}/', $clean, $match)) {
            $email = $match[0];

            // Skip common false positives
            $skipDomains = ['example.com', 'sentry.io', 'wixpress.com', 'w3.org'];
            foreach ($skipDomains as $domain) {
                if (str_contains($email, $domain)) {
                    return null;
                }
            }

            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $email;
            }
        }

        return null;
    }

    private function extractTelLink(\DOMXPath $xpath): ?string
    {
        $nodes = $xpath->query("//a[starts-with(@href, 'tel:')]/@href");

        for ($i = 0; $i < $nodes->length; $i++) {
            $href = $nodes->item($i)->nodeValue;
            $phone = str_replace('tel:', '', $href);
            $phone = preg_replace('/[^\d+\-() ]/', '', $phone);

            if (strlen(preg_replace('/\D/', '', $phone)) >= 7) {
                return $phone;
            }
        }

        return null;
    }

    private function extractPhoneRegex(string $html): ?string
    {
        // Strip scripts and styles
        $clean = preg_replace('#<(script|style)[^>]*>.*?</\1>#si', '', $html);
        $clean = strip_tags($clean);

        // Match US phone formats
        $patterns = [
            '/\(?\d{3}\)?[\s.\-]?\d{3}[\s.\-]?\d{4}/',           // (555) 555-5555 or 555-555-5555
            '/1[\s.\-]?\(?\d{3}\)?[\s.\-]?\d{3}[\s.\-]?\d{4}/',  // 1-555-555-5555
            '/\+1[\s.\-]?\(?\d{3}\)?[\s.\-]?\d{3}[\s.\-]?\d{4}/', // +1-555-555-5555
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $clean, $match)) {
                return trim($match[0]);
            }
        }

        return null;
    }

    private function extractIndustryFromKeywords(\DOMXPath $xpath, string $html): ?string
    {
        $keywords = $this->extractMeta($xpath, 'keywords');
        $description = $this->extractMeta($xpath, 'description')
            ?? $this->extractMeta($xpath, 'og:description');
        $title = $xpath->query('//title')->item(0)?->textContent ?? '';

        $searchText = strtolower(implode(' ', array_filter([$keywords, $description, $title])));

        foreach (self::INDUSTRY_KEYWORDS as $keyword => $industry) {
            if (str_contains($searchText, $keyword)) {
                return $industry;
            }
        }

        return null;
    }
}
